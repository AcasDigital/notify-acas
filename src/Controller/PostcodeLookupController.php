<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/postcode')]
class PostcodeLookupController extends AbstractController
{
    #[Route('/get', name: 'get_postcode', methods: ['GET', 'POST'])]
    public function getAddressData(Request $request, HttpClientInterface $httpClient, NormalizerInterface $normalizer, LoggerInterface $logger): Response
    {
        $jsonData = (array) json_decode((string) $request->getContent(), true);

        $postcode = $jsonData['postcode'] ?? '';
        if ('' == $postcode || !is_string($postcode)) {
            return new JsonResponse(['error' => 'Missing parameter: postcode'], 400);
        }

        $postcodeLookupUrl = $this->getParameter('postcode.lookup_url');
        $data = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
              <soap:Body>
                <LookupPostcode>
                  <postcode>'.$postcode.'</postcode>
                </LookupPostcode>
              </soap:Body>
            </soap:Envelope>';

        $response = $httpClient->request(
            'POST',
            $postcodeLookupUrl,
            [
                'headers' => [
                    'Content-Type' => 'application/soap+xml',
                ],
                'body' => $data,
                'query' => [
                    'encoding' => 'UTF-8',
                ],
            ]
        );
        $responseContent = '';
        try {
            $responseContent = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $response->getContent());
        } catch (HttpExceptionInterface $exception) {
            $logger->error('Error requesting postcode. Message: '.$exception->getMessage());

            return new JsonResponse(['error' => 'There was an error communicating with the service.'], $exception->getCode());
        }

        $array = [];
        if (is_string($responseContent)) {
            $xml = new \SimpleXMLElement($responseContent);
            $body = $xml->xpath('//soapBody')[0];
            $encoded = (string) json_encode((array) $body);
            $array = json_decode($encoded, true);
        }

        $list = [];
        if (is_array($array)) {
            if (!isset($array['LookupPostcodeResponse']['LookupPostcodeResult']['AddressLookupSummaryEntity'])) {
                return new JsonResponse(['error' => 'No address with postcode'], 400);
            }
            $list = $array['LookupPostcodeResponse']['LookupPostcodeResult']['AddressLookupSummaryEntity'];
            if (is_array($list)) {
                foreach ($list as $key => $address) {
                    $list[$key]['Address'] = $address['Address'].', '.strtoupper($postcode);
                }
            }
        }

        $list = $normalizer->normalize($list);

        return new JsonResponse(['list' => $list], $response->getStatusCode());
    }
}
