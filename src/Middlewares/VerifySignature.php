<?php

namespace SoapBox\SignedRequests\Middlewares;

use Closure;
use Illuminate\Contracts\Config\Repository;
use SoapBox\SignedRequests\Requests\Signed;
use SoapBox\SignedRequests\Exceptions\InvalidSignatureException;

class VerifySignature
{
    /**
     * An instance of the configurations repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $configurations;

    /**
     * Expect an instance of the configurations repository so we can lookup
     * where to find our signature, algorithm, and key from.
     *
     * @param \Illuminate\Contracts\Config\Repository $configurations
     *        An instance of the Illuminate configurations repository to lookup
     *        configurations with.
     */
    public function __construct(Repository $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * Applies the middleware to the request before moving onto the next request
     * handler.
     *
     * @throws \SoapBox\SignedRequests\Exceptions\InvalidSignatureException
     *         Thrown when the signature of the request is not valid.
     *
     * @param  \SoapBox\SignedRequests\Requests\Signed $request
     *         An instance of the signed request.
     * @param  \Closure $next
     *         A callback function of where to go next.
     *
     * @return mixed
     */
    public function handle(Signed $request, Closure $next)
    {
        $request
            ->setSignatureHeader($this->configurations->get('signed-requests.headers.signature'))
            ->setAlgorithmHeader($this->configurations->get('signed-requests.headers.algorithm'));

        if (!$request->isValid($this->configurations->get('signed-requests.key'))) {
            throw new InvalidSignatureException();
        }

        return $next($request);
    }
}