<?php

namespace App\Services;

use App\Repositories\ApplicantsRepository;
use Illuminate\Http\Request;

use Storage;
use Carbon\Carbon;

class ApplicantsService {

	public function __construct(ApplicantsRepository $repository) {
		$this->repository = $repository ;
	}

	public function index($queryFilter) {
		return $this->repository->all($queryFilter);
	}

	public function getList() {
		return $this->repository->getList();
    }
    
    public function validateFile($file) {

		$fileToParse = preg_replace('/^data:application\/\w+;base64,/', '', $file);
		$ext = explode(';', $file)[0];
		$ext = explode('/', $ext)[1];

		$find = 'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,';
		$pos = strpos($file, $find);
		if($pos !== false) {
			$fileToParse = str_replace('data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,', '', $file);
			$ext = 'docx';
		}
		$base64File = base64_decode($fileToParse);
		
		return (object)['ext' => $ext, 'content' => $base64File];
	}

	public function create($attributes) {
        if(!Storage::exists('/storage/applicants')) {
            Storage::disk('public')->makeDirectory('applicants',0777, true, true);
        }
        if ($this->repository->checkRecord($attributes['sCI'])) {
            return response()->json([
                'success' => false,
                'message' => 'El Aspirante ya existe'
            ])->setStatusCode(400);
        }
        $date = Carbon::now()->format('Y-m-d');
        $body = [
            'sApellidos' => $attributes['sApellidos'],
            'sNombres' => $attributes['sNombres'],
            'sCI' => $attributes['sCI'],
            'dCreated' => Carbon::now(),
            'sArchivo' => '',
            'picture' => '',
            'nStatus' => 1,
        ];
        $data = $this->repository->create($body);
        if($attributes['pictureFile'] !== null) {
            $hash = bcrypt($data->id.$date);
            $hash = substr($hash,0,20);
            $parseFile = $this->validateFile($attributes['pictureFile']);
            $filename = $attributes['sCI'].'.'.$parseFile->ext;
            
            if($parseFile->ext === 'png' || $parseFile->ext === 'jpg' || $parseFile->ext === 'jpeg' ) {
                if($parseFile->ext === 'jpg' || $parseFile->ext === 'jpeg') {
                    $filename = $date.'-'.$data->id.'-'.$hash.'.png';
                }
                \Image::make($attributes['pictureFile'])->save(public_path('storage/applicants/').$filename);
            } 
            $attr = [ 'picture' => $filename];
            $this->repository->update($data->id, $attr);
        }
        if($attributes['file'] !== null) {
            $hash = bcrypt($data->id.$date);
            $hash = substr($hash,0,20);
            $parseFile = $this->validateFile($attributes['file']);
            $filename = $data->id.'-'.$date.'-'.$hash.'.'.$parseFile->ext;
            
            if($parseFile->ext === 'png' || $parseFile->ext === 'jpg' || $parseFile->ext === 'jpeg' ) {
                if($parseFile->ext === 'jpg' || $parseFile->ext === 'jpeg') {
                    $filename = $date.'-'.$data->id.'-'.$hash.'.png';
                }
                \Image::make($attributes['file'])->save(public_path('storage/applicants/').$filename);
            } else {
                //Storage::disk('payments')->put($filename,$parseFile->content);
                \File::put(public_path(). '/storage/applicants/' . $filename, $parseFile->content);
            }
            $attr = [ 'sArchivo' => $filename];
            $this->repository->update($data->id, $attr);
        }
        return $data;

		

	}

	public function update($request, $id) {
      return $this->repository->update($id, $request);
	}

	public function read($id) {
     return $this->repository->find($id);
	}

	public function delete($id) {
      return $this->repository->delete($id);
	}

	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function search($queryFilter) {
		return $this->repository->search($queryFilter);
     }
     
     public function getCurrentApplicants() {
        return $this->repository->getCurrentApplicants();
    }
}