<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

class CustomAJAXChat extends AJAXChat
{
    public function regenerateSessionID()
    {
        // Never regenerate the session ID
    }

    // Initialize custom request variables:
    public function initCustomRequestVars()
    {
        global $CURUSER;
        //var_dump($CURUSER);
        //die();

        // Auto-login users:
        if (!$this->getRequestVar('login') && !empty($CURUSER)) {
            $this->setRequestVar('login', true);
        }
    }

    // Returns an associative array containing userName, userID and userRole
    // Returns null if login is invalid
    public function getValidLoginUserData()
    {
        global $CURUSER, $class_names;
        //var_dump($CURUSER);
        //die();
        if (!empty($CURUSER) && $CURUSER['enabled'] !== 'no' && $CURUSER['chatpost'] != 0) {
            $userData['userID'] = $CURUSER['id'];
            $userData['userName'] = $this->trimUserName($CURUSER['username']);
            $userData['userClass'] = $class_names[$CURUSER['class']];
            $userData['userRole'] = $CURUSER['class'];
            $userData['channels'] = [0];
            if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
                $userData['channels'] = [0, 1, 2];
            } elseif ($CURUSER['class'] >= UC_MODERATOR) {
                $userData['channels'] = [0, 1];
            }
            return $userData;
        }
    }

    // Store the channels the current user has access to
    // Make sure channel names don't contain any whitespace
    public function &getChannels()
    {
        if ($this->_channels === null) {
            $this->_channels = [];

            $customUsers = $this->getCustomUsers();

            // Get the channels, the user has access to:
            $validChannels = $customUsers[$this->getUserID()]['channels'];
            //file_put_contents('/var/log/nginx/ajaxchat.log', json_encode($validChannels) . PHP_EOL, FILE_APPEND);

            // Add the valid channels to the channel list (the defaultChannelID is always valid):
            foreach ($this->getAllChannels() as $key => $value) {
                if ($value == $this->getConfig('defaultChannelID')) {
                    $this->_channels[$key] = $value;
                    continue;
                }
                // Check if we have to limit the available channels:
                if ($this->getConfig('limitChannelList') && !in_array($value, $this->getConfig('limitChannelList'))) {
                    continue;
                }
                if (in_array($value, $validChannels)) {
                    $this->_channels[$key] = $value;
                }
            }
        }

        return $this->_channels;
    }

    // Store all existing channels
    // Make sure channel names don't contain any whitespace

    public function &getCustomUsers()
    {
        // List containing the registered chat users:
        $users = null;
        require AJAX_CHAT_PATH . 'lib/data/users.php';

        return $users;
    }

    public function &getAllChannels()
    {
        if ($this->_allChannels === null) {
            // Get all existing channels:
            $customChannels = $this->getCustomChannels();

            $defaultChannelFound = false;

            foreach ($customChannels as $name => $id) {
                $this->_allChannels[$this->trimChannelName($name)] = $id;
                if ($id == $this->getConfig('defaultChannelID')) {
                    $defaultChannelFound = true;
                }
            }

            if (!$defaultChannelFound) {
                // Add the default channel as first array element to the channel list
                // First remove it in case it appeard under a different ID
                unset($this->_allChannels[$this->getConfig('defaultChannelName')]);
                $this->_allChannels = array_merge(
                    [
                        $this->trimChannelName($this->getConfig('defaultChannelName')) => $this->getConfig('defaultChannelID'),
                    ],
                    $this->_allChannels
                );
            }
        }

        return $this->_allChannels;
    }

    public function getCustomChannels()
    {
        // List containing the custom channels:
        $channels = null;
        require AJAX_CHAT_PATH . 'lib/data/channels.php';
        // Channel array structure should be:
        // ChannelName => ChannelID
        return array_flip($channels);
    }
}
