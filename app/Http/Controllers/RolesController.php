<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    use ErrorUtil,UserActivityUtil;
     /**
        *
     * @OA\Post(
     *      path="/v1.0/roles",
     *      operationId="createRole",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store role",
     *      description="This method is to store role",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","permissions"},
     *             @OA\Property(property="name", type="string", format="string",example="Rifat"),
     *            @OA\Property(property="permissions", type="string", format="array",example={"user_create","user_update"}),

     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function createRole(RoleRequest $request)
    {
        try{
            $this->storeActivity($request,"");
            if( !$request->user()->hasPermissionTo('role_create'))
            {

               return response()->json([
                  "message" => "You can not perform this action"
               ],401);
          }
           $request_data = $request->validated();
           $role = Role::create(["name"=>$request_data["name"]]);

           $role->syncPermissions($request_data["permissions"]);

           return response()->json([
               "role" =>  $role,
           ], 201);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }





    }
  /**
        *
     * @OA\Put(
     *      path="/v1.0/roles",
     *      operationId="updateRole",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update role",
     *      description="This method is to update role",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","permissions"},
     *             @OA\Property(property="id", type="number", format="number",example="1"),
     *            @OA\Property(property="permissions", type="string", format="array",example={"user_create","user_update"}),

     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function updateRole(RoleUpdateRequest $request) {
        try{
            $this->storeActivity($request,"");
        if( !$request->user()->hasPermissionTo('role_update') )
        {

           return response()->json([
              "message" => "You can not perform this action"
           ],401);
      }
        $updatableData = $request->validated();
        $role = Role::where(["id" => $updatableData["id"]])->first();
        if($role->name == "superadmin" )
        {

           return response()->json([
              "message" => "You can not perform this action"
           ],401);
      }

        $role->syncPermissions($updatableData["permissions"]);


        return response()->json([
            "role" =>  $role,
        ], 201);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }


    }
    /**
        *
     * @OA\Get(
     *      path="/v1.0/roles/{perPage}",
     *      operationId="getRoles",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get roles",
     *      description="This method is to get roles",
     *
    *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),
     *      * *  @OA\Parameter(
* name="start_date",
* in="query",
* description="start_date",
* required=true,
* example="2019-06-29"
* ),
     * *  @OA\Parameter(
* name="end_date",
* in="query",
* description="end_date",
* required=true,
* example="2019-06-29"
* ),
     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function getRoles($perPage,Request $request)
    {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('role_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $rolesQuery =   Role::with('permissions:name,id');

            if(!empty($request->search_key)) {
                $rolesQuery = $rolesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                });

            }



            $roles = $rolesQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($roles, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }


    }

      /**
        *
     * @OA\Get(
     *      path="/v1.0/roles/get/all",
     *      operationId="getRolesAll",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get all roles",
     *      description="This method is to get all roles",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function getRolesAll(Request $request)
    {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('role_view') && !$request->user()->hasPermissionTo('user_view') ){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }


            $roles = Role::with('permissions:name,id')->select("name", "id")->get();
            return response()->json([
                "roles" => $roles,
            ], 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }
        /**
        *
     * @OA\Get(
     *      path="/v1.0/roles/get-by-id/{id}",
     *      operationId="getRoleById",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get role by id",
     *      description="This method is to get role by id",
     *
    *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function getRoleById($id,Request $request) {

        try{
            $this->storeActivity($request,"");
            $role = Role::with('permissions:name,id')
            ->where(["id" => $id])
            ->select("name", "id")->get();
            return response()->json($role, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }

    /**
    *
     * @OA\Delete(
     *      path="/v1.0/roles/{id}",
     *      operationId="deleteRoleById",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to delete role by id",
     *      description="This method is to delete role by id",
     *
    *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function deleteRoleById($id,Request $request) {

        try{
            $this->storeActivity($request,"");
            $initial_roles = config("setup-config.roles");

            $role = Role::where([
                 "id" => $id
                ])->first();

             if(
                 !$request->user()->hasPermissionTo('role_delete')
                 ||
                 in_array($role->name, $initial_roles)
                 )
                 {

                 return response()->json([
                    "message" => "You can not perform this action"
                 ],401);
            }

            Role::where([
             "id" => $id
            ])
            ->delete();

             return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }




    }


   /**
    *
     * @OA\Get(
     *      path="/v1.0/initial-role-permissions",
     *      operationId="getInitialRolePermissions",
     *      tags={"user_management.role"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to get initioal role permissions",
     *      description="This method is to get initioal role permissions",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function getInitialRolePermissions (Request $request) {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('role_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

           $role_permissions = config("setup-config.roles_permission");

           return response()->json($role_permissions,200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }



    }

}
