<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\ReportePagos;

class ReportePagosRepository  {

    public function __construct( ReportePagos $model ) {
      $this->model = $model;

    }
  
    public function all($perPage) {
        $payments = $this->model->query()->with(['cuenta','bancoOrigen'])->paginate($perPage);
        foreach ($payments as $key => $value) {
          if($value->Archivos !== null) {
            $payments[$key]->Archivos = url('storage/reportedPayments/'.$value->Archivos);
          } else {
            $value->Archivos = null;
          }
        }
        return $payments;
    }

    public function findByLogin() {
      $login = auth()->user()->username;

      return $this->model->where('Login', '0084-0084')->get();
    }

    public function find($id) {
        return $this->model->where('idPago', $id)->first();
    }

    public function create($attributes) {
        return $this->model->create($attributes);
      }

    public function update($id, array $attributes) {
        $user = auth()->user()->username;
        if($attributes['status'] == 2) {
          return \DB::connection('sqlsrv_backoffice')->statement('exec sp_PortalProcesarPago ?', array($id));
        }
        return $this->model->where('idPago', $id)->update($attributes);
    }

    public function filter($queryFilter) {
      $searchQuery = $queryFilter;
      $user = auth()->user();
      $search = $this->model->query()->where(function($q) use($searchQuery, $user) {

        if ($searchQuery->query('banco') !== NULL) {
          $query = $searchQuery->query('banco');
          $q->whereHas('bancoOrigen', function($qr) use ($query) {
            $qr->where('cNombreBanco', 'like', "%{$query}%");
          });
        }

        if($searchQuery->query('noInvoice') !== null && $searchQuery->query('noInvoice') == 1 ) {
          $q->where('status', 4);
        } else {
          if ($searchQuery->query('status') !== NULL) {
            $q->where('status', $searchQuery->query('status'));
          }
        }

        if ($searchQuery->query('referencia') !== NULL) {
          $q->where('NroReferencia', 'like', "%{$searchQuery->query('referencia')}%");
        }

        if ($searchQuery->query('dFechaRegistro') !== NULL) {
          $q->where('dFechaRegistro','>=', $searchQuery->query('dFechaRegistro'));
        }

        if ($searchQuery->query('bancoDestino') !== NULL) {
          $q->where('codCuentaDestino', $searchQuery->query('bancoDestino'));
        }

        if($user->share_from !== null && $user->share_to !== null) {
          $q->whereBetween('Login', [ $user->share_from,  $user->share_to]);
        }

        if ($searchQuery->query('accion') !== NULL) {
          $q->where('Login', 'like', "%{$searchQuery->query('accion')}%");
        }
      })->with(['cuenta','bancoOrigen'])->orderBy('dFechaRegistro','ASC')->paginate($searchQuery->query('perPage'));

      foreach ($search as $key => $value) {
        if($value->Archivos !== null) {
          $search[$key]->Archivos = url('storage/reportedPayments/'.$value->Archivos);
        } else {
          $value->Archivos = null;
        }
      }

      return $search;
    }

    public function delete($id) {
      return $this->model->query()->where('idPago', $id)->delete();
     }
 
}