<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Driver\PDOConnection;

use App\Services\SoapService;
use App\BackOffice\Services\ConsultaSaldosService;
use App\BackOffice\Services\EstadoCuentaService;
use App\BackOffice\Services\SaldoService;
use App\BackOffice\Repositories\SaldoRepository;
use App\Repositories\ParameterRepository;

class WebServiceController extends Controller
{
    private $soapService;

    private $consultaSaldosService;

    private $estadoCuentaService;

    private $saldoService;

    private $saldoRepository;

    private $parameterRepository;

    public function __construct(
        SoapService $soapService,
        ConsultaSaldosService $consultaSaldosService,
        EstadoCuentaService $estadoCuentaService,
        SaldoService $saldoService,
        SaldoRepository $saldoRepository,
        ParameterRepository $parameterRepository
    ) {
        $this->soapService = $soapService;
        $this->consultaSaldosService = $consultaSaldosService;
        $this->estadoCuentaService = $estadoCuentaService;
        $this->saldoService = $saldoService;
        $this->saldoRepository = $saldoRepository;
        $this->parameterRepository = $parameterRepository;
    }

    public function getBalance(Request $request)
    {
        $user = auth()->user()->group_id;
        if ($request['isCache'] == "true") {
            return $this->saldoService->index($user);
        }
        $saldo = $this->soapService->getSaldoTotal();
        $vigencia = $this->soapService->getSaldo();
        $vigencia = get_object_vars($vigencia);

        if ($vigencia['status'] == '-1' || $vigencia['status'] == '-4') {
            return $this->saldoService->index($user);
        }

        if ($vigencia['status'] >= 0) {
            $saldoVigencia = $vigencia['saldo'];
        } else {
            $saldoVigencia = 0;
        }
        $data = (object)['saldo' => $saldo, 'status' => $vigencia['status'], 'saldo_vigencia' => $saldoVigencia ];
        $this->saldoRepository->deleteAndInsert($data);
        $data = (object)['saldo' => number_format((float)$saldo, 2), 'status' => $vigencia['status'], 'saldo_vigencia' => $saldoVigencia ];
        return response()->json([
            'cache' => true,
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getUnpaidInvoices(Request $request)
    {
        $user = auth()->user()->group_id;
        if ($request['isCache'] == "true") {
            return $this->consultaSaldosService->index($user);
        }
        return $this->soapService->getUnpaidInvoices($user);
    }

    public function getRenglonesDocumento(Request $request)
    {
        return $this->soapService->getRenglonesDocumento($request['invoice']);
    }

    public function getUnpaidInvoicesByShare(Request $request)
    {
        return $this->soapService->getUnpaidInvoicesByShare($request['share']);
    }

    public function getReportedPayments()
    {
        $data = $this->soapService->getReportedPayments();
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getStatusAccount(Request $request)
    {
        $user = auth()->user()->group_id;
        if ($request['isCache'] == "true") {
            return $this->estadoCuentaService->index($user);
        }
        return $this->soapService->getStatusAccount();
    }

    // @group_id = N'0010-0010',
    // @invoices = N'0010-0010-4-2020-00',
    // @amount = 120,
    // @paymentNumber = N'96459089232984613'

    public function getOrder(Request $request)
    {
        $user = auth()->user()->group_id;
        $data = \DB::connection('sqlsrv_backoffice')->statement(
            'exec sp_PortalProcesarPagoFactura ?,?,?,?,?',
            array($user,$request['invoice'], $request['amount'],$request['order'], $request['dTasa'])
        );

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Error de registro'
            ])->setStatusCode(400);
        }

        return response()->json([
            'success' => true,
            'message' => $data
        ]);
    }

    public function setPaymentOrderChannel(Request $request)
    {
        $user = auth()->user();
        $currency = $this->parameterRepository->findByParameter('MONEDA_DEFAULT')['value'];
        $data = \DB::connection('sqlsrv_backoffice')->statement(
            'exec sp_PortalProcesarPagoFacturaCanal ?,?,?,?,?,?,?',
            [
                $user->group_id,
                $request['invoices'],
                $request['amount'],
                $request['order'],
                $request['channel'],
                $currency,
                $request['dTasa']
            ],
        );

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Error de registro'
            ])->setStatusCode(400);
        }

        return response()->json([
            'success' => true,
            'message' => $data
        ]);
    }

    public function setManualInvoicePayment(Request $request)
    {
        $user = auth()->user()->group_id;
        $data = \DB::connection('sqlsrv_backoffice')->statement(
            'exec sp_PortalPagoFacturaManual ?,?,?,?,?',
            array($request['share'],$request['numFactura'], $request['idPago'], $request['fechaPago'], 'MANUAL')
        );

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Error de registro'
            ])->setStatusCode(400);
        }

        return response()->json([
            'success' => true,
            'message' => $data
        ]);
    }

    public function getTasaDelDia()
    {
        $data = $this->soapService->getTasaDelDia();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
