<?php

namespace App\Http\Controllers\Api;

use App\Models\Assets;
use Illuminate\Http\Request;

class AssetsController extends BaseApiController
{
    private $customAttributes = [
        'group_id' => '单位',
        'ip'       => '资产ip',
        'contact'  => '联系人',
        'phone'    => '联系电话',
    ];

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     */
    public function index(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $group_id      = $request->input('group_id');
        $ip            = $request->input('ip');
        $contact       = $request->input('contact');
        $phone         = $request->input('phone');

        $this->validator();
        $Query      = Assets::when($group_id, function ($query) use ($group_id) {
            return $query->where('group_id', $group_id);
        })->when($ip, function ($query) use ($ip) {
            return $query->where('ip', $ip);
        })->when($contact, function ($query) use ($contact) {
            return $query->where('contact', $contact);
        })->when($phone, function ($query) use ($phone) {
            return $query->where('phone', $phone);
        });
        $countQuery = clone $Query;
        $list       = $Query->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();

        return $this->success(['list' => $list, 'count' => $countQuery->count('id')]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $rules = [
            'group_id' => 'required|exists:group,id|bail',
            'ip'       => 'required|bail',
            'contact'  => 'required|bail',
            'phone'    => 'required|bail',
        ];

        $this->validator($rules, $this->customAttributes);
        $assets           = new  Assets();
        $assets->group_id = $request->input('group_id');
        $assets->ip       = $request->input('ip');
        $assets->contact  = $request->input('contact');
        $assets->phone    = $request->input('phone');
        $assets->save();

        return $this->success();
    }

    /**
     * Display the specified resource.
     * @param  Request  $request
     * @param  Assets  $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Assets $assets)
    {
        return $this->success($assets->toArray());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param  Request  $request
     * @param  Assets  $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     * @throws \Throwable
     */
    public function update(Request $request, Assets $assets)
    {
        $rules = [
            'group_id' => 'required|exists:group,id|bail',
            'ip'       => 'required|bail',
            'contact'  => 'required|bail',
            'phone'    => 'required|bail',
        ];

        $this->validator($rules, $this->customAttributes);

        $assets->group_id = $request->input('group_id');
        $assets->ip       = $request->input('ip');
        $assets->contact  = $request->input('contact');
        $assets->phone    = $request->input('phone');
        $assets->save();

        return $this->success();
    }

    /**
     * Remove the specified resource from storage.
     * @param  Assets  $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function destroy(Assets $assets)
    {
        $assets->delete();
        return $this->success();
    }
}
