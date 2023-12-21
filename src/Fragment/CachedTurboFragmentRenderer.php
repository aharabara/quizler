<?php
namespace App\Fragment;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\SsiFragmentRenderer;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;
use Symfony\Component\HttpKernel\UriSigner;

#[AutoconfigureTag('kernel.fragment_renderer', ['alias' => 'ssi_turbo'])]
class CachedTurboFragmentRenderer extends SsiFragmentRenderer
{
    public function __construct(
        SurrogateInterface $surrogate = null,
        #[Autowire(service: 'fragment_renderer.turbo')] protected FragmentRendererInterface $inlineStrategy,
        UriSigner $signer = null
    )
    {
        parent::__construct($surrogate, $inlineStrategy, $signer);
    }

    public function getName(): string
    {
        return 'ssi_turbo';
    }
}
