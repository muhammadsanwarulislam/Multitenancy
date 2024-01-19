<?php

namespace App\Http\Controllers\Core\Role;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Repository\Role\RoleRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\Role\RoleResource;
use App\Http\Controllers\JsonResponseTrait;
use Repository\Permission\PermissionRepository;
use App\Http\Requests\Role\RoleCreateOrUpdateRequest;
use Illuminate\Http\Request;

class RoleManagmentController extends Controller
{
    use JsonResponseTrait;

    protected $roleRepository, $permissionRepository;

    function __construct(RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            Gate::authorize('view','roles');
            $roles =$this->roleRepository->getAll();
            return $this->successJsonResponse('List of role', RoleResource::collection($roles));
        } catch (\Exception $e) {
            return $this->errorJsonResponse('Error: ' . $e->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleCreateOrUpdateRequest $request)
    {
        try {
            Gate::authorize('edit','roles');
            $role = $this->roleRepository->create($request->validated());
            if($permissions = $request->input('permissions')) {
                foreach(explode(',',$permissions) as $permission_id) {
                    DB::table('role_permission')->insert([
                        'role_id' => $role['id'],
                        'permission_id' => $permission_id
                    ]);
                }
            }
            return $this->createdJsonResponse('Role create successfully',[
                'role'          => new RoleResource($role), 
            ]);
        } catch (\Exception $e) {
            return $this->errorJsonResponse('Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            Gate::authorize('edit','roles');
            $role = $this->roleRepository->findByID($id);
            return $this->successJsonResponse("The role $id is",new RoleResource($role));
        } catch (\Exception $e) {
            return $this->errorJsonResponse('Error: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            Gate::authorize('edit','roles');
            $role = $this->roleRepository->updateByID($id, $request->all());
            DB:: table('role_permission')->where('role_id', $id)->delete();
            if($permissions = $request->input('permissions')) {
                foreach($permissions as $permission_id) {
                    DB::table('role_permission')->insert([
                        'role_id' => $id,
                        'permission_id' => $permission_id
                    ]);
                }
            }
            return $this->createdJsonResponse('Role update successfully');
        } catch (\Exception $e) {
            return $this->errorJsonResponse('Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Gate::authorize('edit','roles');
            DB:: table('role_permission')->where('role_id', $id)->delete();
            $this->roleRepository->deletedByID($id);
            return $this->successJsonResponse('Role delete successfully');
        } catch (\Exception $e) {
            return $this->errorJsonResponse('Error: ' . $e->getMessage());
        }
    }
}
