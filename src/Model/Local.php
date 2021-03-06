<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model;

use Pi;
use Pi\Acl\Acl;
use StdClass;
use Pi\User\Model\AbstractModel;

/**
 * Local user model
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Local extends AbstractModel
{
    /**
     * {@inheritDoc}
     */
    public function load($data, $column = 'id')
    {
        $model = Pi::model('account', 'user');
        if ('id' == $column) {
            $user = $model->find(intval($data));
        } else {
            $user = $model->select(array($column => $data))->current();
        }
        if ($user) {
            $this->assign($user);
        }
        $this->role = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function loadRole()
    {
        if ($this->account->id) {
            $model = ('admin' == Pi::engine()->section()) ? Pi::model('user_staff') : Pi::model('user_role');
            $role = $model->find($this->account->id, 'user');
            $this->role = $role ? $role->role : Acl::GUEST;
        } else {
            $this->role = Acl::GUEST;
        }
        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    public function loadProfile()
    {
        $row = Pi::model('user_profile')->find($this->id);
        $this->profile = $row ? (object) $row->toArray() : new StdClass;
        return $this->profile;
    }

    /**
     * {@inheritDoc}
     */
    public function isGuest()
    {
        return $this->account->id ? false : true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAdmin()
    {
        return $this->role() == Acl::ADMIN ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function isMember()
    {
        return $this->hasRole(Acl::MEMBER)  ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function isStaff()
    {
        return $this->hasRole(Acl::STAFF)  ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role)
    {
        $roles = Pi::service('registry')->role->read($this->role());
        return in_array($role, $roles) ? true : false;
    }
}
