<?php

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsHook;
use MediaWiki\Title\Title;
use MediaWiki\User\UserGroupManager;
use Wikimedia\Rdbms\IConnectionProvider;

class SMWFileProtectHooks implements GetUserPermissionsErrorsHook
{
    private Config $config;

    public function __construct(
        ConfigFactory $configFactory,
        private Config $mainConfig,
        private IConnectionProvider $dbProvider,
        private UserGroupManager $userGroupManager
    ) {
        $this->config = $configFactory->makeConfig('smwfileprotect');
    }

    /**
     * Settings are unprefixed globals ($SMWFileProtectRights, ...), as before 0.5.0.
     */
    public static function makeConfig(): Config
    {
        return new GlobalVarConfig('');
    }

    /**
     * Deny any action on a file when a page that uses it is protected from the user.
     */
    public function onGetUserPermissionsErrors($title, $user, $action, &$result)
    {
        if (!$title->inNamespace(NS_FILE)) {
            return true;
        }

        $groups = $this->userGroupManager->getUserEffectiveGroups($user);
        if (array_intersect($this->config->get('SMWFileProtectRights'), $groups)) {
            return true;
        }

        $referers = $this->loadReferers($title);
        if (!$referers) {
            return true;
        }

        $allowed = (!$this->config->get('SMWFileProtectReferNS')
                || SMWNSProtect::canRead($referers, $groups, $this->mainConfig))
            && (!$this->config->get('SMWFileProtectReferSMW')
                || SMWFileProtect::canRead($referers, $user, $this->config));

        if (!$allowed) {
            $result = false;
            return false;
        }
        return true;
    }

    /**
     * Pages that use the file $title.
     *
     * @return Title[]
     */
    private function loadReferers(Title $title): array
    {
        $rows = $this->dbProvider->getReplicaDatabase()->newSelectQueryBuilder()
            ->select(['page_namespace', 'page_title'])
            ->from('imagelinks')
            ->join('page', null, 'page_id = il_from')
            ->where(['il_to' => $title->getDBkey()])
            ->caller(__METHOD__)
            ->fetchResultSet();

        $referers = [];
        foreach ($rows as $row) {
            $referers[] = Title::makeTitle((int)$row->page_namespace, $row->page_title);
        }
        return $referers;
    }
}
