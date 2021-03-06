<?php

namespace App\Services;

use App\BackOffice\Repositories\ConsultaSaldosRepository;
use App\BackOffice\Repositories\EstadoCuentaRepository;
use App\BackOffice\Repositories\SaldoRepository;
use App\BackOffice\Repositories\TasaCambioRepository;

use SoapClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class SoapService
{
    public function __construct(
        ConsultaSaldosRepository $consultaSaldosRepository,
        EstadoCuentaRepository $estadoCuentaRepository,
        SaldoRepository $saldoRepository,
        TasaCambioRepository $tasaCambioRepository
    ) {
        $this->url = Config::get('webservice.param.WS_SOCIO_URL');
        $this->domain = Config::get('webservice.param.WS_SOCIO_DOMAIN_ID');
        $this->urlExt = Config::get('webservice.param.WS_SOCIOEXT_URL');
        $this->domainExt = Config::get('webservice.param.WS_SOCIOEXT_DOMAIN_ID');
        $this->consultaSaldosRepository = $consultaSaldosRepository;
        $this->estadoCuentaRepository = $estadoCuentaRepository;
        $this->saldoRepository = $saldoRepository;
        $this->tasaCambioRepository = $tasaCambioRepository;
    }

    public function getToken($domain)
    {
        date_default_timezone_set('America/Caracas');
        $domain_id =  $domain;
        $date = date('Ymd');
        $calculated_token = md5($domain_id.$date);
        $calculated_token = base64_encode(strtoupper(md5($domain_id.$date)));
        return $calculated_token;
    }

    public function getWebServiceClient(string $url)
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
        $opts = array(
            'ssl' => array('ciphers'=>'RC4-SHA', 'verify_peer'=>false, 'verify_peer_name'=>false)
        );
        $params = array(
            'encoding' => 'UTF-8',
            'verifypeer' => false,
            'verifyhost' => false,
            'soap_version' => SOAP_1_2,
            'trace' => 1, 'exceptions' => 1,
            "connection_timeout" => 180,
            'stream_context' => stream_context_create($opts),
            'cache_wsdl' => WSDL_CACHE_NONE,
            'keep_alive' => false,
            'ssl_method' => SOAP_SSL_METHOD_SSLv3,
            'location' => $url,
        );
        try {
            return new SoapClient($url, $params);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexion'
            ])->setStatusCode(500);
        }
    }

    public function getSaldo()
    {
        try {
            $url = $this->url;
            $client = $this->getWebServiceClient($url);
            $user = auth()->user()->group_id;
            $response = $client->getSaldoXML([
                'group_id' => $user,
                'token' => $this->getToken($this->domain),
            ])->GetSaldoXMLResult;
            $i = 0;
            $newArray = array();
            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    for ($x = 0; $x < $arrlength; $x++) {
                        array_push($newArray, $registros[$x]);
                    }
                }
                $i++;
            }
            return $newArray[0];
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getSaldoTotal()
    {
        try {
            $url = $this->urlExt;
            $client = $this->getWebServiceClient($url);
            $user = auth()->user()->group_id;
            $response = $client->GetSaldoTotal([
                'group_id' => $user,
                'token' => $this->getToken($this->domainExt),
            ])->GetSaldoTotalResult;
            $i = 0;
            $saldo = explode(";", $response);
            if ($saldo[0]) {
                return (float)$saldo[0];
            }
            return 0;
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
          ])->setStatusCode(500);
        }
    }

    public function getUnpaidInvoices($share)
    {
        try {
            $url = $this->url;
            $client = $this->getWebServiceClient($url);
            $response = $client->GetSaldoDetalladoXML([
                'group_id' => $share,
                'token' => $this->getToken($this->domain),
            ])->GetSaldoDetalladoXMLResult;
            $i = 0;
            $newArrayToInsert = array();
            $newArraytoShow = array();
            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    $acumulado = 0;
                    for ($x = 0; $x < $arrlength; $x++) {
                        $monto = $registros[$x]->saldo;
                        $acumulado = bcadd($acumulado, $monto, 2);
                        $registros[$x]->acumulado = $acumulado;
                        array_push($newArrayToInsert, $registros[$x]);
                        array_push($newArraytoShow, $registros[$x]);
                    }
                }
                $i++;
            }
            $this->consultaSaldosRepository->deleteAndInsert($newArrayToInsert);
            foreach ($newArraytoShow as $key => $value) {
                $newArraytoShow[$key]->originalAmount = $value->saldo;
                $newArraytoShow[$key]->saldo = number_format((float)$value->saldo, 2);
                $newArraytoShow[$key]->total_fac = number_format((float)$value->total_fac, 2);
                $newArraytoShow[$key]->acumulado = number_format((float)$value->acumulado, 2);
            }

            return response()->json([
                'success' => true,
                'data' => $newArraytoShow,
                'total' => $acumulado,
                'cache' => false,
            ]);
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getRenglonesDocumento($doc)
    {
        try {
            $url = $this->urlExt;
            $client = $this->getWebServiceClient($url);
            $response = $client->GetRenglonesDocumentoXML([
                'doc_nro' => $doc,
                'token' => $this->getToken($this->domain),
            ])->GetRenglonesDocumentoXMLResult;
            $i = 0;
            $newArrayToInsert = array();
            $newArraytoShow = array();
            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    $acumulado = 0;
                    for ($x = 0; $x < $arrlength; $x++) {
                        array_push($newArrayToInsert, $registros[$x]);
                        array_push($newArraytoShow, $registros[$x]);
                    }
                }
                $i++;
            }
            return response()->json([
                'success' => true,
                'data' => $newArrayToInsert,
            ]);
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getUnpaidInvoicesByShare($share)
    {
        $currentUser = auth()->user();
        $url = $this->url;
        try {
            $client = $this->getWebServiceClient($url);
            $response = $client->GetSaldoDetalladoXML([
                'group_id' => $share,
                'token' => $this->getToken($this->domain),
            ])->GetSaldoDetalladoXMLResult;
            $i = 0;
            $newArray = array();
            $acumulado = 0;

            if ($currentUser->share_from !== null && $currentUser->share_to !== null && (int)$share < (int)$currentUser->share_from && (int)$share < (int)$currentUser->share_to) {
                return response()->json([
                    'success' => false,
                    'message' => 'La consulta esta fuera de los filtros de su perfil'
                ])->setStatusCode(400);
            }

            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    for ($x = 0; $x < $arrlength; $x++) {
                        if ($registros[$x]->saldo > 0) {
                            $monto = $registros[$x]->saldo;
                            $acumulado = bcadd($acumulado, $monto, 2);
                            $registros[$x]->acumulado = $acumulado;
                            array_push($newArray, $registros[$x]);
                        }
                    }
                }
                $i++;
            }
            foreach ($newArray as $key => $value) {
                $newArray[$key]->originalAmount = $value->saldo;
                $newArray[$key]->saldo = number_format((float)$value->saldo, 2);
                $newArray[$key]->total_fac = number_format((float)$value->total_fac, 2);
                $newArray[$key]->acumulado = number_format((float)$value->acumulado, 2);
            }

            return response()->json([
                'success' => true,
                'data' => $newArray,
                'total' => $acumulado
            ]);
        } catch (SoapFault $fault) {
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getReportedPayments()
    {
        // $url = "http://190.216.224.53:8080/wsServiciosSociosCCC3/wsSociosCCC.asmx?WSDL";
        $url = $this->url;
        try {
            $client = $this->getWebServiceClient($url);
            $user = auth()->user()->group_id;
            $response = $client->GetReportePagosXML([
            'group_id' => $user,
            'token' => $this->getToken($this->domain),
        ])->GetReportePagosXMLResult;
            $i = 0;
            $newArray = array();
            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    for ($x = 0; $x < $arrlength; $x++) {
                        array_push($newArray, $registros[$x]);
                    }
                }
                $i++;
            }
            foreach ($newArray as $key => $value) {
                $newArray[$key]->nMonto = number_format((float)$value->nMonto, 2);
            }
            return $newArray;
        } catch (SoapFault $fault) {
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getStatusAccountByShare($share)
    {
        $url = $this->url;
        $client = $this->getWebServiceClient($url);
        $parametros = [
            'group_id' => $share,
            'token' => $this->getToken($this->domain),
        ];
        try {
            $response = $client->GetEstadoCuentaXML($parametros)->GetEstadoCuentaXMLResult;
            $i = 0;
            $newArraytoShow = array();
            $newArraytoInsert = array();
            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    $acumulado = 0;
                    for ($x = 0; $x < $arrlength; $x++) {
                        $monto = $registros[$x]->total_fac;
                        $acumulado = bcadd($acumulado, $monto, 2);
                        $registros[$x]->acumulado = $acumulado;
                        array_push($newArraytoShow, $registros[$x]);
                        array_push($newArraytoInsert, $registros[$x]);
                    }
                }
                $i++;
            }
            $this->estadoCuentaRepository->deleteAndInsert($newArraytoInsert);

            foreach ($newArraytoShow as $key => $value) {
                $newArraytoShow[$key]->saldo = number_format((float)$value->saldo, 2);
                $newArraytoShow[$key]->total_fac = number_format((float)$value->total_fac, 2);
                $newArraytoShow[$key]->acumulado = number_format((float)$value->acumulado, 2);
            }
            return response()->json([
                'success' => true,
                'data' => $newArraytoShow,
                'total' => $acumulado,
                'cache' => false,
            ]);
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getStatusAccount()
    {
        //$url = "http://190.216.224.53:8080/wsServiciosSociosCCC3/wsSociosCCC.asmx?WSDL";
        $url = $this->url;
        $client = $this->getWebServiceClient($url);
        $user = auth()->user()->group_id;
        $parametros = [
            'group_id' => $user,
            'token' => $this->getToken($this->domain),
        ];
        try {
            $response = $client->GetEstadoCuentaXML($parametros)->GetEstadoCuentaXMLResult;
            $i = 0;
            $newArraytoShow = array();
            $newArraytoInsert = array();
            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    $acumulado = 0;
                    for ($x = 0; $x < $arrlength; $x++) {
                        $monto = $registros[$x]->total_fac;
                        $acumulado = bcadd($acumulado, $monto, 2);
                        $registros[$x]->acumulado = $acumulado;
                        array_push($newArraytoShow, $registros[$x]);
                        array_push($newArraytoInsert, $registros[$x]);
                    }
                }
                $i++;
            }
            $this->estadoCuentaRepository->deleteAndInsert($newArraytoInsert);

            foreach ($newArraytoShow as $key => $value) {
                $newArraytoShow[$key]->saldo = number_format((float)$value->saldo, 2);
                $newArraytoShow[$key]->total_fac = number_format((float)$value->total_fac, 2);
                $newArraytoShow[$key]->acumulado = number_format((float)$value->acumulado, 2);
            }
            return response()->json([
                'success' => true,
                'data' => $newArraytoShow,
                'total' => $acumulado,
                'cache' => false,
            ]);
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }

    public function getTasaDelDia()
    {
        $url = $this->urlExt;
        $client = $this->getWebServiceClient($url);
        $date = date('Y-m-d');

        try {
            $parametros = [
                'mone_co' => 'US$',
                'token' => $this->getToken($this->domainExt),
            ];

            $response = $client->GetUltimaTasaCambioXML($parametros)->GetUltimaTasaCambioXMLResult;
            $i = 0;
            $newArray = array();
            $tasa = '';

            foreach ($response as $key => $value) {
                if ($i==1) {
                    $myxml = simplexml_load_string($value);
                    $registros= $myxml->NewDataSet->Table;
                    $arrlength = @count($registros);
                    $acumulado = 0;
                    for ($x = 0; $x < $arrlength; $x++) {
                        $tasa=  $registros[$x];
                    }
                }
                $i++;
            }

            $attr  = [
                'co_mone' => 'US$',
                'dFecha' => Carbon::parse($tasa->fecha),
                'dTasa' => $tasa->tasa ? $tasa->tasa : -1,
                'dCreated' => Carbon::now(),
            ];

            $this->tasaCambioRepository->store($attr);
            $currentExchange = $this->tasaCambioRepository->getExchange($tasa->tasa ? $tasa->tasa : -1);
            return $currentExchange;
        } catch (SoapFault $fault) {
            echo '<br>'.$fault;
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(500);
        }
    }
}
