<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Core;

use CmsSuggestionBot\Admin\Menu;
use CmsSuggestionBot\Admin\Pages\AdminToolsPage;
use CmsSuggestionBot\Admin\Pages\ApiPage;
use CmsSuggestionBot\Admin\Pages\ConfigurationPage;
use CmsSuggestionBot\Admin\Pages\DashboardPage;
use CmsSuggestionBot\Admin\Pages\HelpPage;
use CmsSuggestionBot\Admin\Pages\KnowledgeBasePage;
use CmsSuggestionBot\Admin\Pages\LogsPage;
use CmsSuggestionBot\Admin\Pages\ReaderPage;
use CmsSuggestionBot\Admin\Pages\SettingsPage;
use CmsSuggestionBot\Ajax\BotAjaxController;
use CmsSuggestionBot\Ajax\CacheAjaxController;
use CmsSuggestionBot\Bot\AnswerResolver;
use CmsSuggestionBot\Bot\BotEngine;
use CmsSuggestionBot\Cache\CacheBuilder;
use CmsSuggestionBot\Cache\ChunkHasher;
use CmsSuggestionBot\Contracts\CacheStorageInterface;
use CmsSuggestionBot\Cron\Scheduler;
use CmsSuggestionBot\Front\ChatWidget;
use CmsSuggestionBot\Installer\Installer;
use CmsSuggestionBot\Logger\Logger;
use CmsSuggestionBot\Readers\FileReader;
use CmsSuggestionBot\Readers\PageReader;
use CmsSuggestionBot\Readers\PostReader;
use CmsSuggestionBot\Readers\ReaderManager;
use CmsSuggestionBot\Repositories\ApiKeyRepository;
use CmsSuggestionBot\Repositories\CacheRepository;
use CmsSuggestionBot\Repositories\ChunkRepository;
use CmsSuggestionBot\Repositories\ConversationRepository;
use CmsSuggestionBot\Repositories\JobRepository;
use CmsSuggestionBot\Repositories\KnowledgeRepository;
use CmsSuggestionBot\Repositories\LogRepository;
use CmsSuggestionBot\Repositories\MessageRepository;
use CmsSuggestionBot\Repositories\QueueRepository;
use CmsSuggestionBot\Repositories\ReaderRunRepository;
use CmsSuggestionBot\Repositories\SettingsRepository;
use CmsSuggestionBot\Repositories\StatisticsRepository;
use CmsSuggestionBot\Services\AiProviderRegistry;
use CmsSuggestionBot\Services\ApiKeyService;
use CmsSuggestionBot\Services\CacheService;
use CmsSuggestionBot\Services\CommonQuestionsService;
use CmsSuggestionBot\Services\GreetingsService;
use CmsSuggestionBot\Services\KnowledgeService;
use CmsSuggestionBot\Services\MaintenanceService;
use CmsSuggestionBot\Services\ReaderService;
use CmsSuggestionBot\Services\RestrictedWordsService;
use CmsSuggestionBot\Services\SettingsService;
use CmsSuggestionBot\Services\UsageLimitService;
use CmsSuggestionBot\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Composition root: every service/repository binding lives here, in one
 * place, so the object graph stays easy to trace. Nothing outside this
 * class should call `new` on a Service, Repository, or Admin\Pages\* class -
 * resolve it from the container instead (or, more commonly, just accept it
 * as a constructor argument on a class the container already builds).
 */
final class Plugin {

	use Singleton;

	private Container $container;

	private function init(): void {
		$this->container = new Container();
		$this->registerServices();
	}

	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
			static::$instance->init();
		}

		return static::$instance;
	}

	public function boot(): void {
		Installer::maybeUpgrade();

		/** @var Menu $menu */
		$menu = $this->container->get( Menu::class );
		$menu->hooks();

		/** @var CacheAjaxController $cacheAjax */
		$cacheAjax = $this->container->get( CacheAjaxController::class );
		$cacheAjax->hooks();

		/** @var BotAjaxController $botAjax */
		$botAjax = $this->container->get( BotAjaxController::class );
		$botAjax->hooks();

		/** @var ChatWidget $widget */
		$widget = $this->container->get( ChatWidget::class );
		$widget->hooks();

		/** @var Scheduler $scheduler */
		$scheduler = $this->container->get( Scheduler::class );
		$scheduler->hooks();
	}

	private function registerServices(): void {
		$c = $this->container;

		// ── Repositories ─────────────────────────────────────────────────
		$c->bind( ChunkRepository::class, static fn() => new ChunkRepository() );
		$c->bind( CacheRepository::class, static fn( Container $c ) => new CacheRepository( $c->get( ChunkRepository::class ) ) );
		$c->bind( CacheStorageInterface::class, static fn( Container $c ) => $c->get( CacheRepository::class ) );
		$c->bind( SettingsRepository::class, static fn() => new SettingsRepository() );
		$c->bind( KnowledgeRepository::class, static fn() => new KnowledgeRepository() );
		$c->bind( LogRepository::class, static fn() => new LogRepository() );
		$c->bind( ApiKeyRepository::class, static fn() => new ApiKeyRepository() );
		$c->bind( StatisticsRepository::class, static fn() => new StatisticsRepository() );
		$c->bind( JobRepository::class, static fn() => new JobRepository() );
		$c->bind( QueueRepository::class, static fn() => new QueueRepository() );
		$c->bind( ConversationRepository::class, static fn() => new ConversationRepository() );
		$c->bind( MessageRepository::class, static fn() => new MessageRepository() );
		$c->bind( ReaderRunRepository::class, static fn() => new ReaderRunRepository() );

		// ── Cross-cutting ────────────────────────────────────────────────
		$c->bind( Logger::class, static fn( Container $c ) => new Logger( $c->get( LogRepository::class ) ) );
		$c->bind( SettingsService::class, static fn( Container $c ) => new SettingsService( $c->get( SettingsRepository::class ) ) );
		$c->bind( RestrictedWordsService::class, static fn( Container $c ) => new RestrictedWordsService( $c->get( SettingsService::class ) ) );
		$c->bind( AiProviderRegistry::class, static fn( Container $c ) => new AiProviderRegistry( $c->get( SettingsService::class ) ) );
		$c->bind( UsageLimitService::class, static fn( Container $c ) => new UsageLimitService( $c->get( SettingsService::class ) ) );
		$c->bind( GreetingsService::class, static fn( Container $c ) => new GreetingsService( $c->get( SettingsService::class ) ) );
		$c->bind( CommonQuestionsService::class, static fn( Container $c ) => new CommonQuestionsService(
			$c->get( KnowledgeRepository::class ),
			$c->get( SettingsService::class )
		) );
		$c->bind( KnowledgeService::class, static fn( Container $c ) => new KnowledgeService( $c->get( KnowledgeRepository::class ) ) );
		$c->bind( ApiKeyService::class, static fn( Container $c ) => new ApiKeyService( $c->get( ApiKeyRepository::class ) ) );
		$c->bind( MaintenanceService::class, static fn( Container $c ) => new MaintenanceService(
			$c->get( KnowledgeRepository::class ),
			$c->get( Logger::class )
		) );

		// ── Readers ──────────────────────────────────────────────────────
		$c->bind( PageReader::class, static fn() => new PageReader() );
		$c->bind( PostReader::class, static fn() => new PostReader() );
		$c->bind( FileReader::class, static fn() => new FileReader( CSB_RESOURCES_DIR ) );
		$c->bind( ReaderManager::class, static fn( Container $c ) => new ReaderManager( array(
			$c->get( PageReader::class ),
			$c->get( PostReader::class ),
			$c->get( FileReader::class ),
		) ) );
		$c->bind( ReaderService::class, static fn( Container $c ) => new ReaderService(
			$c->get( ReaderManager::class ),
			$c->get( ReaderRunRepository::class ),
			$c->get( Logger::class )
		) );

		// ── Cache pipeline ───────────────────────────────────────────────
		$c->bind( ChunkHasher::class, static fn() => new ChunkHasher() );
		$c->bind( CacheBuilder::class, static fn( Container $c ) => new CacheBuilder(
			$c->get( ReaderManager::class ),
			$c->get( CacheStorageInterface::class ),
			$c->get( ChunkHasher::class )
		) );
		$c->bind( CacheService::class, static fn( Container $c ) => new CacheService(
			$c->get( CacheBuilder::class ),
			$c->get( CacheStorageInterface::class ),
			$c->get( ReaderManager::class ),
			$c->get( ReaderService::class ),
			$c->get( SettingsService::class ),
			$c->get( Logger::class )
		) );

		$c->bind( Scheduler::class, static fn( Container $c ) => new Scheduler( $c->get( CacheService::class ) ) );

		// ── Bot ──────────────────────────────────────────────────────────
		$c->bind( AnswerResolver::class, static fn( Container $c ) => new AnswerResolver(
			$c->get( RestrictedWordsService::class ),
			$c->get( GreetingsService::class ),
			$c->get( CommonQuestionsService::class ),
			$c->get( KnowledgeRepository::class ),
			$c->get( ChunkRepository::class ),
			$c->get( AiProviderRegistry::class ),
			$c->get( SettingsService::class )
		) );
		$c->bind( BotEngine::class, static fn( Container $c ) => new BotEngine(
			$c->get( AnswerResolver::class ),
			$c->get( ConversationRepository::class ),
			$c->get( MessageRepository::class ),
			$c->get( KnowledgeRepository::class ),
			$c->get( UsageLimitService::class ),
			$c->get( SettingsService::class )
		) );

		// ── Ajax / Front ─────────────────────────────────────────────────
		$c->bind( CacheAjaxController::class, static fn( Container $c ) => new CacheAjaxController( $c->get( CacheService::class ) ) );
		$c->bind( BotAjaxController::class, static fn( Container $c ) => new BotAjaxController( $c->get( BotEngine::class ) ) );
		$c->bind( ChatWidget::class, static fn( Container $c ) => new ChatWidget(
			$c->get( SettingsService::class ),
			$c->get( AiProviderRegistry::class )
		) );

		// ── Admin ────────────────────────────────────────────────────────
		$c->bind( DashboardPage::class, static fn( Container $c ) => new DashboardPage(
			$c->get( SettingsService::class ),
			$c->get( CacheService::class ),
			$c->get( ReaderService::class ),
			$c->get( KnowledgeRepository::class )
		) );
		$c->bind( AdminToolsPage::class, static fn( Container $c ) => new AdminToolsPage(
			$c->get( ReaderManager::class ),
			$c->get( CacheService::class ),
			$c->get( MaintenanceService::class )
		) );
		$c->bind( ConfigurationPage::class, static fn( Container $c ) => new ConfigurationPage( $c->get( SettingsService::class ) ) );
		$c->bind( ReaderPage::class, static fn( Container $c ) => new ReaderPage( $c->get( ReaderService::class ) ) );
		$c->bind( KnowledgeBasePage::class, static fn( Container $c ) => new KnowledgeBasePage(
			$c->get( KnowledgeService::class ),
			$c->get( CommonQuestionsService::class )
		) );
		$c->bind( LogsPage::class, static fn( Container $c ) => new LogsPage( $c->get( LogRepository::class ) ) );
		$c->bind( ApiPage::class, static fn( Container $c ) => new ApiPage(
			$c->get( ApiKeyService::class ),
			$c->get( SettingsService::class )
		) );
		$c->bind( SettingsPage::class, static fn() => new SettingsPage() );
		$c->bind( HelpPage::class, static fn() => new HelpPage() );

		$c->bind( Menu::class, static fn( Container $c ) => new Menu(
			$c->get( DashboardPage::class ),
			$c->get( AdminToolsPage::class ),
			$c->get( ConfigurationPage::class ),
			$c->get( ReaderPage::class ),
			$c->get( KnowledgeBasePage::class ),
			$c->get( LogsPage::class ),
			$c->get( ApiPage::class ),
			$c->get( SettingsPage::class ),
			$c->get( HelpPage::class )
		) );
	}
}
