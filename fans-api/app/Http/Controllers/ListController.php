<?php

namespace App\Http\Controllers;

use App\Models\ListType;
use App\Services\ListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ListController extends Controller
{
    protected $listService;

    public function __construct(ListService $listService)
    {
        $this->listService = $listService;
    }

    public function index()
    {
        $user = Auth::user();
        $lists = $this->listService->getAllLists($user);
        return response()->json(['data' => $lists]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $list = $this->listService->createList($user, $validatedData);
        return response()->json(['data' => $list], 201);
    }

    public function show($list)
    {
        $user = Auth::user();
        $listDetails = $this->listService->getListDetails($user, $list);

        if (!$listDetails) {
            return response()->json(['message' => 'List not found'], 404);
        }

        $members = $this->listService->getListMembers($user, $list);

        return response()->json([
            'list' => $listDetails,
            'members' => $members
        ]);
    }

    public function members($list)
    {
        $user = Auth::user();
        $members = $this->listService->getListMembers($user, $list);
        return response()->json(['data' => $members]);
    }

    public function addMember($list, $userId)
    {
        $user = Auth::user();
        $memberToAdd = \App\Models\User::findOrFail($userId);
        $this->listService->addToList($user, $list, $memberToAdd);
        return response()->json(['message' => 'Member added successfully']);
    }

    public function removeMember($list, $userId)
    {
        $user = Auth::user();
        $memberToRemove = User::findOrFail($userId);

        // Check if it's a default list
        $listType = ListType::find($list);
        if ($listType && $listType->is_default) {
            $list = $listType->name;
        }
        $result = $this->listService->removeFromList($user, $list, $memberToRemove);
        
        if ($result['success']) {
            return response()->json(['message' => $result['message']]);
        } else {
            return response()->json(['message' => $result['message']], 400);
        }
    }

    public function destroy($listId)
    {
        $user = Auth::user();
        $this->listService->deleteList($user, $listId);
        return response()->json(['message' => 'List deleted successfully']);
    }
}