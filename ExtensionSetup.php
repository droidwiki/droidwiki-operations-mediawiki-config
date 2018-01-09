<?php
# DO NOT PUT PRIVATE INFORMATION HERE!

$extToLoad = [
	'AntiSpoof', # needed by AbuseFilter
	'Cite', # ref-tags
	'ParserFunctions',
	'SyntaxHighlight_GeSHi',
	'CanonURL',
	'InputBox',
	'OpenGraphMeta',
	'MultimediaViewer',
	'CommonsMetadata',
	'CookieWarning',
	'Dereferer',
	'EmbedVideo', # Allows to embed YouTube videos into wikipages
	'Disambiguator',
	'DynamicPageList',
	'Echo',
	'CyanogenModDev',
	'BetaFeatures',
	'PageImages', # PageImages (needed by MobileFrontend and HoverCards)
	'TextExtracts', # TextExtracts (needed by MobileFrontend and HoverCards)
	'Popups',
	'ExpandTamplates',
	'MwEmbedSupport',
	'TimedMediaHandler',
	'ConfirmEdit',
	'AbuseFilter',
	'StopForumSpam',
	'Elastica',
	'CirrusSearch',
	'WikiEditor',
	'CodeEditor',
	'googleAnalytics',
	'MobileFrontend',
	'Scribunto',
	'Thanks',
	'GoogleLogin',
	'CentralNotice',
	'GoogleAnalyticsTopPages',
	'TemplateData',
	'VisualEditor',
	'QuickSearchLookup',
	'UserMerge',
	'Gadgets',
	'Citoid',
	'Interwiki',
	'TemplateSandbox',
	'LdapAuthentication',
	'UniversalLanguageSelector',
	'Translate',
	'GoogleSiteLinksSearchBox',
	'OATHAuth',
	'GeoData',
	'XenForoAuth',
	'CiteThisPage',
	'ContentTranslation',
	'GlobalUsage',
];

$extensionsToLoadWithExtensionregistration = [];
foreach ( $extToLoad as $name ) {
	$useExtensionConfigName = 'wmgUse' . $name;
	if ( isset( $$useExtensionConfigName ) && !$$useExtensionConfigName ) {
		continue;
	}

	$extensionInformation = wfGetExtensionInformation( $name );
	if ( $extensionInformation['exists'] ) {
		switch( $extensionInformation['installType'] ) {
			case 'json':
				$extensionsToLoadWithExtensionregistration[] = $name;
				break;
			default:
				require_once "$IP/extensions/$name/$name.php";
		}
	}
}

wfLoadExtensions( $extensionsToLoadWithExtensionregistration );

function wfExtensionExists( $name ) {
	return wfGetExtensionInformation( $name )['exists'];
}

function wfGetExtensionInformation( $name ) {
	global $IP;

	$retval = [
		'exists' => false,
		'installType' => null,
	];
	if ( file_exists( "$IP/extensions/$name/$name.php" ) ) {
		$retval['exists'] = true;
		$retval['installType'] = 'php';
	}
	if ( file_exists( "$IP/extensions/$name/extension.json" ) ) {
		$retval['exists'] = true;
		$retval['installType'] = 'json';
	}
	return  $retval;
}

# Configuration for ConfirmEdit
if ( wfExtensionExists( "ConfirmEdit" ) ) {
	if ( PHP_SAPI === 'cli' ) {
		$wgMessagesDirs['ReCaptchaNoCaptcha'] = "$IP/extensions/ConfirmEdit/ReCaptchaNoCaptcha/i18n";
		$wgMessagesDirs['FancyCaptcha'] = "$IP/extensions/ConfirmEdit/FancyCaptcha/i18n";
	}
	// The DroidWiki app can't handle client side JavaScript (on which reCaptcha is based on)
	// Check, if the request is made via the api (and assume, that this is the app or any other client that
	// needs machine readable format's) and use the FancyCaptcha plugin instead of reCaptcha.
	if ( $_SERVER['SCRIPT_NAME'] !== '/api.php' && isset( $_GET['title'] ) && strpos( $_GET['title'], 'Captcha/image' ) === false ) {
		require_once "$IP/extensions/ConfirmEdit/ReCaptchaNoCaptcha.php";
		$wgReCaptchaSiteKey = $wmgReCaptchaSiteKey;
		$wgReCaptchaSecretKey = $wmgReCaptchaSecretKey;
		$wgCaptchaClass = 'ReCaptchaNoCaptcha';
	} else {
		require_once "$IP/extensions/ConfirmEdit/FancyCaptcha.php";
		$wgCaptchaDirectory = $wmgFancyCaptchaCaptchaDir;
		$wgCaptchaSecret = $wmgFancyCaptchaSecretKey;
		$wgCaptchaClass = 'FancyCaptcha';
		// in order to work with clients other then web browsers, the CAPTCHA information needs to be
		// stored in th cache, instead of in the session (which is the default).
		$wgCaptchaStorageClass = 'CaptchaCacheStore';
	}

	# only emailconfirmed can skip captcha
	$wgGroupPermissions['*']['skipcaptcha'] = false;
	$wgGroupPermissions['user']['skipcaptcha'] = false;
	$wgGroupPermissions['autoconfirmed']['skipcaptcha'] = false;
	$wgGroupPermissions['bot']['skipcaptcha'] = false;
	$wgGroupPermissions['sysop']['skipcaptcha'] = false;
	$wgGroupPermissions['emailconfirmed']['skipcaptcha'] = true;
	$ceAllowConfirmedEmail = true;

	# Trigger for ConfirmEdit
	$wgCaptchaTriggers['edit'] = true;
	$wgCaptchaTriggers['create'] = true;
	$wgCaptchaTriggers['addurl'] = true;
	$wgCaptchaTriggers['createaccount'] = true;
	$wgCaptchaTriggers['badlogin'] = true;
}

# AbuseFilter
if ( wfExtensionExists( "AbuseFilter" ) ) {
	$wgGroupPermissions['sysop']['abusefilter-modify'] = true;
	$wgGroupPermissions['*']['abusefilter-log-detail'] = true;
	$wgGroupPermissions['*']['abusefilter-view'] = true;
	$wgGroupPermissions['*']['abusefilter-log'] = true;
	$wgGroupPermissions['sysop']['abusefilter-private'] = true;
	$wgGroupPermissions['sysop']['abusefilter-modify-restricted'] = true;
	$wgGroupPermissions['sysop']['abusefilter-revert'] = true;
}

# Stop Forum Spam
if ( wfExtensionExists( "StopForumSpam" ) ) {
	$wgSFSAPIKey = $wmgSFSAPIKey;
	$wgPutIPinRC = true;
}

# Elasticsearch
if ( wfExtensionExists( "Elastica" ) && wfExtensionExists( "CirrusSearch" ) ) {
	$wgSearchType = 'CirrusSearch';
	$wgCirrusSearchPowerSpecialRandom = $wmgCirrusSearchPowerSpecialRandom;
	$wgCirrusSearchServers = [ '188.68.49.74' ];
}

# WikiEditor/graphical Editor
if ( wfExtensionExists( "WikiEditor" ) ) {
	$wgDefaultUserOptions['usebetatoolbar'] = 1;
	$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
	$wgDefaultUserOptions['wikieditor-preview'] = 0;
}

# CodeEditor (extension for WikiEditor
if ( wfExtensionExists( "CodeEditor" ) ) {
	# Enable it on JS/CSS pages
	$wgCodeEditorEnableCore = true;
}

# Add Google-Analytics
if ( wfExtensionExists( "googleAnalytics" ) ) {
	$wgGoogleAnalyticsAccount = $wmgGoogleAnalyticsAccount;
	$wgGoogleAnalyticsIgnoreSysops = $wmgGoogleAnalyticsIgnoreSysops;
	$wgGoogleAnalyticsIgnoreBots = $wmgGoogleAnalyticsIgnoreBots;
}

# MobileFrontend
if ( wfExtensionExists( "MobileFrontend" ) ) {
	wfLoadSkin( 'MinervaNeue' );
	$wgMobileFrontendLogo = "{$wgScriptPath}/static/images/project-logos/androide.png";
	$wgMFAutodetectMobileView = true;
	$wgMFEnableBeta = true;
	$wgMFSpecialCaseMainPage = true;
	$wgMFAllowNonJavaScriptEditing = true;

	if ( $wmgUseWikibaseClient ) {
		$wgMFUseWikibaseDescription = true;
		$wgMFDisplayWikibaseDescription = true;
	}
}

# Scribunto
if ( wfExtensionExists( "Scribunto" ) ) {
	$wgScribuntoDefaultEngine = 'luastandalone';
	$wgScribuntoUseGeSHi = true;
	$wgScribuntoUseCodeEditor = true;
}

if ( $wmgUseDroidWiki && wfExtensionExists( "DroidWiki" ) ) {
	require_once "$IP/extensions/DroidWiki/DroidWiki.php";
	$wgDroidWikiAdDisallowedNamespaces = [ 120, 121, 122, 123 ];
}

# Thanks
if ( wfExtensionExists( "Thanks" ) ) {
	$wgIncludejQueryMigrate = true;
	$wgThanksConfirmationRequired = true;
}

# GoogleLogin
if ( wfExtensionExists( "GoogleLogin" ) ) {
	$wgGLSecret = $wmgGLSecret;
	$wgGLAppId = $wmgGLAppId;
	$wgGLAPIKey = $wmgGLAPIKey;
	$wgGLShowCreateReason = true;
	$wgGLShowRight = true;
	// FIXME: reset these two to default value, after mw-ui buttons fixed in HTMLForm
	$wgGLShowKeepLogin = false;
	$wgGLForceKeepLogin = true;
	$wgGLAllowAccountCreation = true;
}

# CentralNotice - 01.08.2014
if ( wfExtensionExists( "CentralNotice" ) ) {
	$wgCentralDBname = 'droidwikiwiki';
	if ( $multiversion->getDBName() === $wgCentralDBname ) {
		$wgNoticeInfrastructure = true;
	}
	$wgCentralPagePath = "//www.droidwiki.org/w/index.php";
	$wgCentralSelectedBannerDispatcher = "//www.droidwiki.org/w/index.php?title=Special:BannerLoader";
	$wgNoticeProjects = [ 'droidwikiwiki', 'datawiki' ];
	$wgNoticeProject = $multiversion->getWikiName();
}

if ( wfExtensionExists ( "GoogleAnalyticsTopPages" ) ) {
	$wgGATPProfileId = $wmgGATPProfileId;
	$wgGATPKeyFileLocation = $wmgGATPKeyFileLocation;
	$wgGATPServiceAccountName = $wmgGATPServiceAccountName;
}

# TemplateData
if ( wfExtensionExists( "TemplateData" ) ) {
	$wgTemplateDataUseGUI = true;
}

# VisualEditor
if ( wfExtensionExists ( "VisualEditor" ) ) {
	$wgDefaultUserOptions['visualeditor-enable'] = 1;
	$wgVisualEditorSupportedSkins = [ 'vector', 'apex', 'monobook', 'minerva' ];
	$wgVisualEditorEnableWikitext = true;
	$wgVisualEditorAvailableNamespaces = [
		NS_TALK => true,
		NS_USER => true,
		NS_USER_TALK => true,
		NS_PROJECT => true,
		NS_PROJECT_TALK => true,
		NS_FILE => true,
		NS_FILE_TALK => true,
		NS_HELP => true,
		NS_HELP_TALK => true,
		NS_CATEGORY => true,
		NS_CATEGORY_TALK => true,
	];
}

if ( wfExtensionExists ( 'CookieWarning' ) ) {
	$wgCookieWarningEnabled = true;
	$wgCookieWarningMoreUrl =
		'https://www.droidwiki.org/DroidWiki:Impressum#Verwendung_von_Cookies_.28Cookie-Policy.29';
}

if ( wfExtensionExists ( 'UserMerge' ) ) {
	$wgGroupPermissions['bureaucrat']['usermerge'] = true;
}

if ( wfExtensionExists( 'Citoid' ) ) {
	$wgCitoidServiceUrl = 'https://go2tech.de/citoid/api';
}

if ( wfExtensionExists( 'Interwiki' ) ) {
	$wgGroupPermissions['sysop']['interwiki'] = true;
}

if ( $wmgUseLdapAuthentication && wfExtensionExists( 'LdapAuthentication' ) ) {
	$wgAuth = new LdapAuthenticationPlugin();
	$wgLDAPDomainNames = [
		'go2tech.de',
	];
	$wgLDAPServerNames = [
		'go2tech.de' => 'localhost',
	];
	$wgLDAPUseLocal = false;
	$wgLDAPEncryptionType = [
		'go2tech.de' => 'clear',
	];
	$wgLDAPPort = [
		'go2tech.de' => 389,
	];
	$wgLDAPSearchAttributes = [
		'go2tech.de' => 'uid',
	];
	$wgLDAPBaseDNs = [
		'go2tech.de' => 'dc=go2tech,dc=de',
	];
	$wgLDAPWriterDN = [
		'go2tech.de' => 'cn=mwldapwriter,ou=users,dc=go2tech,dc=de'
	];
	$wgLDAPWriterPassword = [
		'go2tech.de' => $wmgLDAPWriterPassword,
	];
	$wgLDAPProxyAgent = [
		'go2tech.de' => 'cn=mwldapwriter,ou=users,dc=go2tech,dc=de',
	];
	$wgLDAPProxyAgentPassword = [
		'go2tech.de' => $wmgLDAPWriterPassword,
	];
	$wgLDAPMailPassword = [
		'go2tech.de' => true
	];
	$wgLDAPPreferences = [
		'go2tech.de' => [ 'email' => 'mail', 'realname' => 'displayName' ]
	];
	$wgLDAPUpdateLDAP = [
		'go2tech.de' => true
	];
	$wgLDAPGroupUseFullDN = [ "go2tech.de" => false ];
	$wgLDAPGroupObjectclass = [ "go2tech.de" => "posixgroup" ];
	$wgLDAPGroupAttribute = [ "go2tech.de" => "memberuid" ];
	$wgLDAPGroupSearchNestedGroups = [ "go2tech.de" => false ];
	$wgLDAPGroupNameAttribute = [ "go2tech.de" => "cn" ];
	$wgLDAPRequiredGroups = [ "go2tech.de" => [ "cn=opswiki,ou=Groups,dc=go2tech,dc=de" ] ];
	$wgLDAPLowerCaseUsername = [
		'go2tech.de' => true,
	];
}
if ( $wmgUseTranslate && wfExtensionExists( 'Translate' ) ) {
	$wgTranslateTranslationServices = [
		'Yandexm' => [
			'key' => $wmgTranslateTranslationServicesKeys['Yandex'],
			'url' => 'https://translate.yandex.net/api/v1.5/tr.json/translate',
			'pairs' => 'https://translate.yandex.net/api/v1.5/tr.json/getLangs',
			'timeout' => 3,
			'langorder' => [ 'en', 'ru', 'uk', 'de', 'fr', 'pl', 'it', 'es', 'tr' ],
			'langlimit' => 1,
			'type' => 'yandex',
		],
	];
}

if ( wfExtensionExists( 'TemplateSandbox' ) ) {
	$wgTemplateSandboxEditNamespaces[] = 828;
}

$wmgWikibaseBaseNs = 120;
// Define custom namespaces. Use these exact constant names.
define( 'WB_NS_ITEM', $wmgWikibaseBaseNs );
define( 'WB_NS_ITEM_TALK', $wmgWikibaseBaseNs + 1 );
define( 'WB_NS_PROPERTY', $wmgWikibaseBaseNs + 2 );
define( 'WB_NS_PROPERTY_TALK', $wmgWikibaseBaseNs + 3 );

if ( $wmgUseWikibaseRepo ) {
	$wgEnableWikibaseRepo = true;
	require_once "$IP/extensions/Wikibase/repo/Wikibase.php";

	$wgContentHandlerUseDB = true;
	// Register extra namespaces.
	$wgExtraNamespaces[WB_NS_ITEM] = 'Item';
	$wgExtraNamespaces[WB_NS_ITEM_TALK] = 'Item_talk';
	$wgExtraNamespaces[WB_NS_PROPERTY] = 'Property';
	$wgExtraNamespaces[WB_NS_PROPERTY_TALK] = 'Property_talk';

	$wgWBRepoSettings['entityNamespaces'] = [
		'item' => WB_NS_ITEM,
		'property' => WB_NS_PROPERTY
	];

	// Tell Wikibase which namespace to use for which kind of entity
	// Make sure we use the same keys on repo and clients, so we can share cached objects.
	$wgWBRepoSettings['sharedCacheKeyPrefix'] = $wgDBname . ':WBL/' . rawurlencode( WBL_VERSION );
	// NOTE: no need to set up $wgNamespaceContentModels, Wikibase will do that automatically based on $wgWBRepoSettings
	// Tell MediaWIki to search the item namespace
	$wgNamespacesToBeSearchedDefault[WB_NS_ITEM] = true;
	// the special group includes all the sites in the specialSiteLinkGroups,
	// grouped together in a 'Pages linked to other sites' section.
	$wgWBRepoSettings['siteLinkGroups'] = [
		'droidwiki',
		'wikipedia',
		'special'
	];
	// these are the site_group codes as listed in the sites table
	$wgWBRepoSettings['specialSiteLinkGroups'] = [ 'commons', 'wikidata' ];

	$wgWBRepoSettings['statementSections'] = [
		'item' => [
			'statements' => null,
			'identifiers' => [
				'type' => 'dataType',
				'dataTypes' => [ 'external-id' ],
			],
		],
	];

	$wgWBRepoSettings['localClientDatabases'] = [
		'droidwiki' => 'droidwikiwiki',
		'endroidwiki' => 'endroidwikiwiki',
	];
	$wgWBRepoSettings['formatterUrlProperty'] = 'P9';
	$wgContentNamespaces = array_merge( $wgContentNamespaces, [ WB_NS_ITEM, WB_NS_PROPERTY ] );
}

if ( $wmgUseWikibaseClient ) {
	$wgEnableWikibaseClient = true;
	require_once "$IP/extensions/Wikibase/client/WikibaseClient.php";

	wfLoadExtension( 'WikibaseCreateLink' );

	$wgWBClientSettings['entityNamespaces'] = [
		'item' => WB_NS_ITEM,
		'property' => WB_NS_PROPERTY
	];
	$wgWBClientSettings['siteGlobalID'] = substr( $wgDBname, 0, -4 );
	$wgWBClientSettings['siteGroup'] = 'droidwiki';
	$wgWBClientSettings['repoUrl'] = 'https://data.droidwiki.org';
	$wgWBClientSettings['repoArticlePath'] = '/wiki/$1';
	$wgWBClientSettings['repoScriptPath'] = '/w';
	$wgWBClientSettings['repoDatabase'] = 'datawiki';
	$wgWBClientSettings['changesDatabase'] = 'datawiki';
	$wgWBCLientSettings['injectRecentChanges'] = true;
	$wgWBClientSettings['languageLinkSiteGroup'] = 'droidwiki';

	$wgWBClientSettings['repoNamespaces'] = [
		'item' => 'Item',
		'property' => 'Property',
	];

	$wgWBClientSettings['entityNamespaces'] = [
		'item' => $wmgWikibaseBaseNs,
		'property' => $wmgWikibaseBaseNs + 2,
	];

	$wgWBClientSettings['repoSiteName'] = 'DroidWiki Data';
	$wgWBClientSettings['otherProjectsLinks'] = [ 'wikidatawiki', 'commonswiki', 'dewiki', 'enwiki' ];
	$wgWBClientSettings['otherProjectsLinksByDefault'] = true;
	$wgWBClientSettings['sendEchoNotification'] = true;

	$wgHooks['WikibaseClientOtherProjectsSidebar'][] = function ( Wikibase\DataModel\Entity\ItemId $itemId, array &$sidebar ) {
		foreach ( $sidebar as $id => &$group ) {
			foreach ( $group as $siteId => &$attributes ) {
				if ( isset( $attributes['hreflang'] ) ) {
					$attributes['msg'] = $attributes['msg'] . '-' . $attributes['hreflang'];
				}
			}
		}
		return true;
	};
}

if ( $wmgUseOATHAuth && wfExtensionExists( 'OATHAuth' ) ) {
	$wgSharedTables[] = 'oathauth_users';
}

if ( wfExtensionExists( 'GeoData' ) ) {
	$wgGeoDataBackend = 'elastic';
}

if ( $wmgUseXenForoAuth && wfExtensionExists( 'XenForoAuth' ) ) {
	$wgXenForoAuthBaseUrl = 'http://android-hilfe.de/api/';
	$wgXenForoAuthClientId = 'ca9why0jle';
	$wgXenForoAuthClientSecret = $wmgXenForoAuthClientSecret;
	$wgXenForoAuthButtonIcon = '\'../../../static/images/android-hilfe_xenforoauth_logo.png\'';
	$wgXenForoAuthAutoCreate = true;
}

if ( $wmgUseContentTranslation && wfExtensionExists( 'ContentTranslation' ) ) {
	$wgContentTranslationRESTBase = [
		'url' => 'https://www.droidwiki.org/api/v1',
		'fixedUrl' => true,
		'timeout' => 100000,
		'HTTPProxy' => false,
		'forwardCookies' => false,
	];
	$wgContentTranslationDatabase = 'droidwikiwiki';
	$wgContentTranslationSiteTemplates = [
		'view' => '//$1.droidwiki.org/wiki/$2',
		'action' => '//$1.droidwiki.org/w/index.php?title=$2',
		'api' => '//$1.droidwiki.org/w/api.php',
		'cx' => 'https://go2tech.de/cxserver/v1',
		'cookieDomain' => '.droidwiki.org',
		'restbase' => '//$1.droidwiki.org/api/v1',
	];
	$wgContentTranslationDefaultSourceLanguage = 'de';
	$wgContentTranslationTranslateInTarget = true;
	$wgContentTranslationDomainCodeMapping = [
		'de' => 'www',
	];
}

if ( wfExtensionExists( 'GlobalUsage' ) ) {
	$wgGlobalUsageDatabase = 'droidwikiwiki';
}
