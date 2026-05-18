<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected int $initialOutputBufferLevel = 0;

    public function createApplication()
    {
        $this->pinTestingDatabaseToSqliteMemory();

        $app = parent::createApplication();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['db']->setDefaultConnection('sqlite');

        return $app;
    }

    protected function setUp(): void
    {
        $this->pinTestingDatabaseToSqliteMemory();

        parent::setUp();

        \Illuminate\Support\Facades\Cache::clear();

        $this->enforceSafeTestingDatabaseConnection();
        $this->initialOutputBufferLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->initialOutputBufferLevel) {
            @ob_end_clean();
        }

        parent::tearDown();
    }

    /**
     * Force sqlite memory before RefreshDatabase trait runs migrate:fresh.
     *
     * @return array<class-string, int>
     */
    protected function setUpTraits()
    {
        $this->pinTestingDatabaseToSqliteMemory();

        if (isset($this->app)) {
            $this->app['config']->set('database.default', 'sqlite');
            $this->app['config']->set('database.connections.sqlite.database', ':memory:');
            $this->app['db']->setDefaultConnection('sqlite');
        }

        return parent::setUpTraits();
    }

    private function pinTestingDatabaseToSqliteMemory(): void
    {
        putenv('APP_ENV=testing');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
    }

    private function enforceSafeTestingDatabaseConnection(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $default = (string) config('database.default');
        $driver = (string) config("database.connections.{$default}.driver");
        $database = (string) config('database.connections.sqlite.database');

        if ($default !== 'sqlite' || $driver !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException('Safety guard: tests must run on sqlite :memory: only.');
        }
    }
}
