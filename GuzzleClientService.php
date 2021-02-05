<?php
declare (strict_types = 1);

namespace app\service;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use JC\HttpClient\JCRequest;

class GuzzleClientService  extends \think\Service
{

    const LOG_LEVEL = 'guzzle';

    private $logger;

    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register()
    {
        $this->app->bind('GuzzleClient', JCRequest::class);
    }

    
    /**
     * 执行服务
     *
     * @return mixed
     */
    public function boot()
    {
        $messageFormats = [
            "{request}\n{response}",
        ];

        $stack = $this->setLoggingHandler($messageFormats);
        JCRequest::setHandler($stack);
    }

    /**
     * Setup Logger
     */
    private function getLogger()
    {
        if (!$this->logger) {
            //$this->logger = new Logger('guzzle');
            //$this->logger->pushHandler(new RotatingFileHandler(runtime_path() . 'guzzle.log'));
            $this->logger = $this->app->log;
        }

        return $this->logger;
    }
    /**
     * Setup Middleware
     */
    private function setGuzzleMiddleware(string $messageFormat)
    {
        return Middleware::log(
            $this->getLogger(),
            new MessageFormatter($messageFormat),
            self::LOG_LEVEL
        );
    }
    /**
     * Setup Logging Handler Stack
     */
    private function setLoggingHandler(array $messageFormats)
    {
        $stack = HandlerStack::create();

        collect($messageFormats)->each(function ($messageFormat) use ($stack) {
            // We'll use unshift instead of push, to add the middleware to the bottom of the stack, not the top
            $stack->unshift(
                $this->setGuzzleMiddleware($messageFormat)
            );
        });

        return $stack;
    }
}
