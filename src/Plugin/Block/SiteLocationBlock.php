<?php

namespace Drupal\site_location\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\site_location\CurrentTimeBasedOnTimeZone;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Provides a 'Site Location Block' Block.
 *
 * @Block(
 *   id = "sitelocation_block",
 *   admin_label = @Translation("Site Location block"),
 *   category = @Translation("Site Location"),
 * )
 */
class SiteLocationBlock extends BlockBase implements ContainerFactoryPluginInterface {

    /** Custom service
     * 
     * @var Drupal\site_location\CurrentTimeBasedOnTimeZone
     */
    protected $currentTimeBasedOnTimeZone;

    /** The current user
     * 
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * The kill switch.
     *
     * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
     */
    protected $killSwitch;

    /** The Module Handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandler
     */
    protected $moduleHandler;

    /**
     * Class constructor
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxy $currentUser, KillSwitch $killSwitch, ModuleHandler $moduleHandler, CurrentTimeBasedOnTimeZone $currentTimeBasedOnTimeZone) {
        $this->currentTimeBasedOnTimeZone = $currentTimeBasedOnTimeZone;
        $this->currentUser = $currentUser;
        $this->killSwitch = $killSwitch;
        $this->moduleHandler = $moduleHandler;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

        return new static(
                $configuration, $plugin_id, $plugin_definition, $container->get('current_user'), $container->get('page_cache_kill_switch'), $container->get('module_handler'), $container->get('site_location.currenttime')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function build() {

        $siteLocationDetailsArr = $this->currentTimeBasedOnTimeZone->siteLocationAndCurentTime();
        if ($this->currentUser->isAnonymous() && $this->moduleHandler->moduleExists('page_cache')) {
            $this->killSwitch->trigger();
        }
        $build['current_time'] = [
            '#theme' => 'block--sitelocationblock',
            '#data' => $siteLocationDetailsArr,
        ];

        return $build;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheContexts() {
        return Cache::mergeContexts(
                        parent::getCacheContexts(), ['current_time']
        );
    }

}
