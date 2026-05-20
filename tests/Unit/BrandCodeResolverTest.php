<?php

namespace Tests\Unit;

use App\Services\Catalog\BrandCodeResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BrandCodeResolverTest extends TestCase
{
    #[Test]
    public function it_parses_brand_definition_text(): void
    {
        $resolver = new BrandCodeResolver;
        $text = <<<'TXT'
A number:

10 = Augusta
81 = BADGER SPORT
60 = Russell (Team)
TXT;

        $map = $resolver->parseBrandDefinitionText($text);

        $this->assertSame('Augusta', $map['10']);
        $this->assertSame('BADGER SPORT', $map['81']);
        $this->assertSame('Russell (Team)', $map['60']);
    }

    #[Test]
    public function it_resolves_name_from_config(): void
    {
        config(['brand_codes.spec_path' => 'imports/momentec_spec_missing.xlsx']);

        $resolver = new BrandCodeResolver;
        $resolver->forgetCache();

        $this->assertSame('BADGER SPORT', $resolver->nameForCode('81'));
        $this->assertSame('Augusta', $resolver->nameForCode('10'));
    }
}
