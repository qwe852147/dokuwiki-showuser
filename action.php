<?php
/**
 * DokuWiki Plugin showuser (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  pochin <qwe852147@hotmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_showuser extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'print_user', array());
        $controller->register_hook('TPL_ACT_UNKNOWN','BEFORE',$this,'html');
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'handle_template_pagetools_display');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function print_user(Doku_Event $event) {
        global $ACT , $ID;
        if($ACT != 'show_user')
            return false;
        if(auth_quickaclcheck($ID) < AUTH_READ){ 
            $ACT = ""; 
            return false;
        } 
        $event->preventDefault();
        return true;
    }

    function html(Doku_Event $event) {
        global $ACT;
        if($ACT!= 'show_user') return;
        $event->preventDefault();

        if(!$this->getConf('is_for_admin') || ($this->getConf('is_for_admin')&& auth_isadmin())){
            $userlist = $this->get_has_permission_users();
            echo "<table>";
            echo "<tr>";
            echo "<th> ".$this->getLang('user_id')." </th>";
            echo "<th> ".$this->getLang('user_name')." </th>";
            echo "</tr>";

            foreach($userlist as $userid => $username){
                echo "<tr>";
                echo "<td>".$userid."</td>";
                echo "<td>".$username."</td>";
                echo "</tr>";
            }
            echo "</table>";
        }else{
            echo $this->getLang('error_msg');
        }
    }

    function get_has_permission_users(){
        global $ID , $auth;
        $user_list = Array();
        $all_user_list = $auth->retrieveUsers();
        foreach($all_user_list as $user => $userinfo){
            extract($userinfo);
            if(!$this->getConf('is_include_admin')){
                $grps = array_values(array_diff($grps, ['admin']));
            }
            if(auth_aclcheck($ID, $user, $grps, false)> 0){
                $user_list[$user] = $name;
            }
        }
        return $user_list;
    }

    public function handle_template_pagetools_display(Doku_Event &$event, $param) {
        global $ID ;
        if(!$this->getConf('is_for_admin') || ($this->getConf('is_for_admin')&& auth_isadmin())){
            $params = array('do' => 'show_user');
            // insert button at position before last (up to top)
            $event->data['items'] = array_slice($event->data['items'], 0, -1, true) +
                array('show_user' =>
                        '<li>'
                        . '<a href="' . wl($ID, $params) . '"  class="action showuser" rel="nofollow" title="' . $this->getLang('show_user_btn') . '">'
                        . '<span>' . $this->getLang('show_user_btn') . '</span>'
                        . '</a>'
                        . '</li>'
                ) +
                array_slice($event->data['items'], -1, 1, true);
        }
    }
}

// vim:ts=4:sw=4:et:
