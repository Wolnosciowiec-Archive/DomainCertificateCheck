<?php declare(strict_types=1);

namespace Wolnosciowiec\DomainCertificateCheck\Check;

use Wolnosciowiec\DomainCertificateCheck\Exception\ConnectionError;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * @author Krzysztof Wesołowski <wesoly.krzysztofa@gmail.com>
 * @url www.zsp.net.pl
 */
class DomainCertificateCheck implements CheckInterface
{
    protected $domains              = [];
    protected $daysRemainingToWarn = 7;
    protected $daysRemainingToFail  = 2;

    public function __construct(array $domains, int $daysRemainingToWarn = 7, int $daysRemainingToFail = 2)
    {
        $this->domains = $domains;
        $this->daysRemainingToWarn = $daysRemainingToWarn;
        $this->daysRemainingToFail = $daysRemainingToFail;
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $summary = [];

        foreach ($this->domains as $domainName) {
            try {
                $daysToExpire = $this->getDomainDaysToExpire($domainName);

            } catch (ConnectionError $e) {
                return new Failure($e->getMessage());
            }


            if ($daysToExpire <= $this->daysRemainingToFail) {
                return new Failure('Domain "' . $domainName . '" will expire in ' . $daysToExpire . ' days');

            } elseif ($daysToExpire <= $this->daysRemainingToWarn) {
                return new Warning('Domain "' . $domainName . '" will expire in ' . $daysToExpire . ' days');
            }

            $summary[] = $domainName . '=' . $daysToExpire . ' days';
        }

        return new Success(implode(', ', $summary));
    }

    protected function getDomainDaysToExpire(string $domainName)
    {
        $expirationDate = $this->getCertificateExpirationDate($this->getCertificateInformation($domainName));
        return $expirationDate->diff(new \DateTime())->days;
    }

    protected function getCertificateExpirationDate(array $data): \DateTime
    {
        return new \DateTime(date('Y-m-d H:i:s', $data['validTo_time_t']));
    }

    protected function getCertificateInformation(string $domain)
    {
        $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
        $handle = @stream_socket_client(
            "ssl://" . $domain . ":443",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($handle)) {
            throw new ConnectionError('Cannot connect to ' . $domain . ', probably the host is down');
        }

        $cert = openssl_x509_parse(stream_context_get_params($handle)["options"]["ssl"]["peer_certificate"]);
        return $cert;
    }

    public function getLabel()
    {
        return 'Domains certificates';
    }
}
