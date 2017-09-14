<?php

namespace Code16\Sharp\Http\Api;

class EntityListController extends ApiController
{

    /**
     * @param string $entityKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($entityKey)
    {
        sharp_check_ability("entity", $entityKey);

        $list = $this->getListInstance($entityKey);
        $list->buildListConfig();

        return response()->json([
            "containers" => $list->dataContainers(),
            "layout" => $list->listLayout(),
            "data" => $list->data(),
            "config" => $list->listConfig()
        ]);
    }

    /**
     * Call for reorder instances.
     *
     * @param string $entityKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($entityKey)
    {
        sharp_check_ability("update", $entityKey);

        $list = $this->getListInstance($entityKey);
        $list->reorderHandler()->reorder(
            request("instances")
        );

        return response()->json([
            "ok" => true
        ]);
    }
}