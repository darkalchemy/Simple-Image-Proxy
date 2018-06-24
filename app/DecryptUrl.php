<?php

namespace ImageProxy;

use Intervention\Image\ImageManager;
use Blocktrail\CryptoJSAES\CryptoJSAES;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class DecryptUrl
{
    private $keys;

    /**
     * DecryptUrl constructor.
     *
     * @param array $keys
     */
    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @param $request
     *
     * @return mixed|null
     */
    public function decrypt_url($request)
    {
        $uri = $request->getUri()->getQuery();
        $uri = explode('&uid=', base64_decode($uri));

        if (empty($uri[1]) || !array_key_exists($uri[1], $this->keys)) {
            try {
                $image = $this->make_image(__DIR__ . '/../images/noposter.png');
            } catch (\Exception $e) {
                return null;
            }

            return $image->response();
        }
        $url = CryptoJSAES::decrypt($uri[0], $this->keys[$uri[1]]);
        if (empty($url)) {
            try {
                $image = $this->make_image(__DIR__ . '/../images/noposter.png');
            } catch (\Exception $e) {
                return null;
            }

            return $image->response();
        }
        $pieces = parse_url($url);
        $width = $height = '';
        if (!empty($pieces['query'])) {
            parse_str($pieces['query'], $query);
            $width = isset($query['width']) ? $query['width'] : '';
            $height = isset($query['height']) ? $query['height'] : '';
            unset($query['width'], $query['height'], $pieces['scheme'], $pieces['query']);
            $pieces = implode('', $pieces) . '?' . http_build_query($query);
        } else {
            $width = $height = '';
            $pieces = implode('', $pieces);
        }
        $pieces = trim($pieces, '?');

        if (!empty($pieces)) {
            $hash = hash('sha512', $pieces);
            $path = __DIR__ . "/../images/$hash";

            if (file_exists($path)) {
                try {
                    $image = $this->make_image($path, $width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } catch (\Exception $e) {
                    return null;
                }

                return $image->response();
            } else {
                $client = new \GuzzleHttp\Client([
                    'sink' => $path,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
                    ],
                ]);
                $response = $client->request('GET', $url);
                if ($response->getStatusCode() == 200) {
                    try {
                        $image = $this->make_image($path, $width, $height, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    } catch (\Exception $e) {
                        return null;
                    }

                    return $image->response();
                }
            }
        }
        $image = $this->make_image(__DIR__ . '/../images/noposter.png');

        return $image->response();
    }

    protected function optimize($path)
    {
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($path);
    }

    protected function make_image($path, $width = null, $height = null)
    {
        $manager = new ImageManager();

        if (!empty($width) || !empty($height)) {
            try {
                $image = $manager->make($path)->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } catch (\Exception $e) {
                return null;
            }
        } else {
            try {
                $image = $manager->make($path);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $image;
    }
}
