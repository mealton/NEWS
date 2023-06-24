<?php


class ProfileModel extends MainModel
{

    public function init(){

    }

    public function auth($username, $password)
    {
        $user = $this->getter('users', ['username' => $username, 'password' => $password, 'is_active' => 1]);
        $user = $user[0];
        /*if($user['is_banned']){
            $banned_date = $user['banned_date'];
            $period = $user['banned_pedriod'];
            $unbanned_date = strtotime($banned_date) + $period;
            if(time() > $unbanned_date)
                return false;
        }*/
        return $user;
    }

}