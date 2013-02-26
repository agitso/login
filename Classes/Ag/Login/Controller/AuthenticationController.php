<?php
namespace Ag\Login\Controller;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class AuthenticationController extends \TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController {

	/**
	 * @param \TYPO3\Flow\Mvc\View\ViewInterface $view
	 */
	protected function initializeView(\TYPO3\Flow\Mvc\View\ViewInterface $view) {
		parent::initializeView($view);

		$this->view->assignMultiple(array(
			'css' => 'resource://Ag.Login/' . $this->generateCss()
		));
	}

	/**
	 * @return string
	 */
	protected function generateCss() {
		$css = new \Assetic\Asset\AssetCollection(array(
			new \Assetic\Asset\GlobAsset(FLOW_PATH_PACKAGES . 'Application/Ag.Login/Resources/Public/css/*.scss')
		), array(new \Assetic\Filter\Sass\SassFilter()));

		$cache = new \Assetic\Asset\AssetCache($css, new \Assetic\Cache\FilesystemCache(FLOW_PATH_PACKAGES . 'Application/Ag.Login/Resources/Public/css/build/cache/'));

		$path = 'Public/css/build/aglogin.' . $cache->getLastModified() . '.css';
		$target = FLOW_PATH_PACKAGES . 'Application/Ag.Login/Resources/' . $path;

		if (!file_exists($target)) {
			file_put_contents($target, $cache->dump());
		}

		return $path;
	}


	/**
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		if ($originalRequest !== NULL) {
			$this->redirectToRequest($originalRequest);
		}

		$this->redirect($this->settings['onAuthenticationSuccess']['action'], $this->settings['onAuthenticationSuccess']['controller'], $this->settings['onAuthenticationSuccess']['package']);
	}

	/**
	 * @return void
	 */
	public function logoutAction() {
		parent::logoutAction();

		$this->redirect($this->settings['onLogout']['action'], $this->settings['onLogout']['controller'], $this->settings['onLogout']['package']);
	}

	/**
	 * @return string
	 */
	public function loggedInAction() {
		return 'Successfully logged in as ' . $this->securityContext->getAccount()->getAccountIdentifier();
	}


}

?>