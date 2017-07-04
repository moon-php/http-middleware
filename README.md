# Moon - HTTP Middleware
A very simple HTTP Middleware implementation

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/moon-php/http-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/moon-php/http-middleware/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/moon-php/http-middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/moon-php/http-middleware/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/moon-php/http-middleware/badges/build.png?b=master)](https://scrutinizer-ci.com/g/moon-php/http-middleware/build-status/master)

**Accpeted as [awesome psr-15 middleware](https://github.com/middlewares/awesome-psr15-middlewares#packages) package**

## Documentation

- Delegate

- InvalidArgumentException



### Delegate

__namespace: Moon\HttpMiddleware\Delegate__

According to the PSR15 proposal : 

_The DelegateInterface defines a single method that accepts a request and returns a response._

_The delegate interface must be implemented by any middleware dispatcher that uses middleware implementing MiddlewareInterface._

So i decided to make the **Delegate** a **Middleware Dispatcher** itself.

The object is really simple, it has a constructor that requires an **array of MiddlewareInterface** implementor and a callable to use as **default** response.

The MiddlewareInterface implementor will be directly implemented by the userland and inserted into the array.

    $request = new ServerRequestImplementor();
    $delegate = new Delegate([new MiddlewareOne(), new MiddlewareTwo(), new MiddlewareThree()], new DefaultResponse());
    $delegate->process($request);

### InvalidArgumentException

__namespace Moon\HttpMiddleware\Exception\InvalidArgumentException__

If an object passed into the array is not a MiddlewareInterface implementor and **InvalidArgumentException** will be thrown.
