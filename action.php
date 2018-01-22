<?php
/**
 * DokuWiki Plugin newnamespacepermissions (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_newnamespacepermissions extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleActionActPreprocess');
        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'BEFORE', $this, 'checkPagesave');

    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handleActionActPreprocess(Doku_Event $event, $param) {
        if (act_clean($event->data) !== 'edit') {
            return;
        }

        if ($this->isSaveAllowed()) {
            return;
        }

        // user tries to create namespace but isn't allowed to do so
        $event->data = 'show';
        msg($this->getLang('namespace creation not allowed'),-1);
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function checkPagesave(Doku_Event $event, $param) {
        if ($this->isSaveAllowed()) {
            return;
        }

        $event->preventDefault();
        msg($this->getLang('namespace creation not allowed'), -1);
        msg($this->getLang('you tried to save') . '<pre>' . hsc($event->data['newContent']) . '</pre>', 0);
    }

    /**
     * Check if we're creating a new page in a new namespace and if we're allowed to do so
     */
    protected function isSaveAllowed() {
        global $ID, $INPUT, $USERINFO;
        $namespaceDir = dirname(wikiFN($ID));
        if (file_exists($namespaceDir) && is_dir($namespaceDir)) {
            return true;
        }

        $allowedToCreateNamespaces = $this->getConf('allow_namespace_creation');
        $user = $INPUT->server->str('REMOTE_USER');
        $groups = $USERINFO['grps'] ?: ['ALL'];

        if (auth_isMember($allowedToCreateNamespaces, $user, $groups) || auth_isadmin()) {
            return true;
        }

        return false;
    }

}

// vim:ts=4:sw=4:et:
