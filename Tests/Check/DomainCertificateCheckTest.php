<?php declare(strict_types=1);

namespace Wolnosciowiec\DomainCertificateCheck\Tests\Check;

use Wolnosciowiec\DomainCertificateCheck\Check\DomainCertificateCheck;
use Wolnosciowiec\DomainCertificateCheck\Tests\TestCase;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

class DomainCertificateCheckTest extends TestCase
{
    public function provideDomainData()
    {
        return [
            'valid domains' => [
                'domains' => [
                    'google.com', 'wolnosciowiec.net',
                ],
                'daysToWarn' => 7,
                'daysToFail' => 2,
                'result' => Success::class,
            ],

            'non existing domain' => [
                'domains' => [
                    'example-not-working-.com',
                ],

                'daysToWarn' => 30,
                'daysToFail' => 20,
                'result' => Failure::class,
            ],

            'expiring domain' => [
                'domains' => [
                    'wladzaprecz.pl',
                ],

                'daysToWarn' => 860,
                'daysToFail' => 850,
                'result' => Failure::class,
            ],
        ];
    }

    /**
     * @dataProvider provideDomainData()
     *
     * @param array  $domains
     * @param int    $daysToWarn
     * @param int    $daysToFail
     * @param string $resultClassName
     */
    public function testCheck(array $domains, int $daysToWarn, int $daysToFail, string $resultClassName)
    {
        $check = new DomainCertificateCheck($domains, $daysToWarn, $daysToFail);
        $this->assertSame($resultClassName, get_class($check->check()));
    }
}
