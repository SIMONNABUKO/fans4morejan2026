<?php

namespace App\Services;

use App\Models\Lists;
use App\Models\ListType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class ListService
{
    private const DEFAULT_LISTS = [
        ['name' => 'VIP', 'description' => 'VIP users'],
        ['name' => 'Followers', 'description' => 'Users following you'],
        ['name' => 'Following', 'description' => 'Users you follow'],
        ['name' => 'Subscribed', 'description' => 'Users you subscribe to'],
        ['name' => 'Blocked Accounts', 'description' => 'Users you have blocked'],
        ['name' => 'Muted Accounts', 'description' => 'Users you have muted']
    ];

    public function createDefaultListTypes(): void
    {
        try {
            foreach (self::DEFAULT_LISTS as $list) {
                ListType::firstOrCreate(
                    ['name' => $list['name']],
                    [
                        'description' => $list['description'],
                        'is_default' => true
                    ]
                );
            }
            Log::info('Default list types created successfully');
        } catch (QueryException $e) {
            Log::error('Failed to create default list types', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function assignDefaultListsToUser(User $user): void
    {
        try {
            $defaultListTypes = ListType::where('is_default', true)->get();
            foreach ($defaultListTypes as $listType) {
                $user->listTypes()->syncWithoutDetaching([$listType->id]);
            }
            Log::info('Default lists assigned to user', ['user_id' => $user->id]);
        } catch (QueryException $e) {
            Log::error('Failed to assign default lists to user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getAllLists(User $user): Collection
    {
        try {
            $this->ensureUserHasDefaultLists($user);

            // Get default lists with member counts
            $defaultLists = DB::table('list_types')
                ->leftJoin('list_members', function ($join) use ($user) {
                    $join->on('list_types.id', '=', 'list_members.list_type_id')
                        ->where('list_members.user_id', '=', $user->id);
                })
                ->select(
                    'list_types.id',
                    'list_types.name',
                    'list_types.description',
                    DB::raw('COUNT(DISTINCT list_members.member_id) as members_count')
                )
                ->where('list_types.is_default', true)
                ->groupBy('list_types.id', 'list_types.name', 'list_types.description')
                ->get()
                ->map(function ($list) {
                    return [
                        'id' => $list->id,
                        'name' => $list->name,
                        'count' => $list->members_count,
                        'description' => $list->description,
                        'is_default' => true
                    ];
                });

            // Get custom lists with member counts
            $customLists = DB::table('lists')
                ->leftJoin('list_members', function ($join) {
                    $join->on('lists.id', '=', 'list_members.list_id');
                })
                ->where('lists.user_id', $user->id)
                ->select(
                    'lists.id',
                    'lists.name',
                    'lists.description',
                    DB::raw('COUNT(DISTINCT list_members.member_id) as members_count')
                )
                ->groupBy('lists.id', 'lists.name', 'lists.description')
                ->get()
                ->map(function ($list) {
                    return [
                        'id' => $list->id,
                        'name' => $list->name,
                        'count' => $list->members_count,
                        'description' => $list->description,
                        'is_default' => false
                    ];
                });

            return $defaultLists->concat($customLists);
        } catch (QueryException $e) {
            Log::error('Failed to get all lists', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function ensureUserHasDefaultLists(User $user): void
    {
        try {
            $defaultListTypes = ListType::where('is_default', true)->get();
            $userDefaultListTypes = $user->listTypes()
                ->where('is_default', true)
                ->pluck('list_types.id');

            $missingDefaultListTypes = $defaultListTypes->whereNotIn('id', $userDefaultListTypes);

            if ($missingDefaultListTypes->isNotEmpty()) {
                $user->listTypes()->attach($missingDefaultListTypes->pluck('id'));
                Log::info('Assigned missing default lists to user', [
                    'user_id' => $user->id,
                    'assigned_lists' => $missingDefaultListTypes->pluck('name')
                ]);
            }
        } catch (QueryException $e) {
            Log::error('Failed to ensure user has default lists', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getListMembers(User $user, $listId): Collection
    {
        Log::info('🔍 FETCHING LIST MEMBERS', [
            'user_id' => $user->id, 
            'list_id' => $listId,
            'list_id_type' => gettype($listId)
        ]);

        try {
            $listType = ListType::find($listId);
            
            Log::info('🔍 LIST TYPE CHECK', [
                'user_id' => $user->id,
                'list_id' => $listId,
                'list_type_found' => $listType ? true : false,
                'list_type_name' => $listType ? $listType->name : null,
                'is_default' => $listType ? $listType->is_default : null
            ]);
            
            if ($listType && $listType->is_default) {
                Log::info('🔍 PROCESSING DEFAULT LIST', [
                    'user_id' => $user->id,
                    'list_type_name' => $listType->name
                ]);
                
                // Fetch members for default list
                switch ($listType->name) {
                    case 'Following':
                        $members = $user->following()->get();
                        break;
                    case 'Followers':
                        $members = $user->followers()->get();
                        break;
                    case 'Subscribed':
                        $members = $user->subscriptions()
                            ->where('status', \App\Models\Subscription::ACTIVE_STATUS)
                            ->with('creator')
                            ->get()
                            ->pluck('creator');
                        break;
                    case 'Blocked Accounts':
                    case 'Muted Accounts':
                        Log::info('🔍 FETCHING BLOCKED/MUTED ACCOUNTS', [
                            'user_id' => $user->id,
                            'list_type_name' => $listType->name,
                            'list_type_id' => $listType->id
                        ]);
                        
                        // Check what's in the list_members table
                        $rawMembers = DB::table('list_members')
                            ->where('list_type_id', $listType->id)
                            ->where('user_id', $user->id)
                            ->get();
                            
                        Log::info('🔍 RAW LIST MEMBERS QUERY', [
                            'user_id' => $user->id,
                            'list_type_id' => $listType->id,
                            'raw_members_count' => $rawMembers->count(),
                            'raw_members' => $rawMembers->toArray()
                        ]);
                        
                        $members = User::whereIn('id', function ($query) use ($listType, $user) {
                            $query->select('member_id')
                                ->from('list_members')
                                ->where('list_type_id', $listType->id)
                                ->where('user_id', $user->id);
                        })->get();
                        
                        Log::info('🔍 BLOCKED/MUTED MEMBERS RESULT', [
                            'user_id' => $user->id,
                            'list_type_name' => $listType->name,
                            'members_count' => $members->count(),
                            'member_ids' => $members->pluck('id')->toArray(),
                            'member_names' => $members->pluck('name')->toArray()
                        ]);
                        break;
                    default:
                        // Fall back to list_members table for other default lists
                        $members = User::whereIn('id', function ($query) use ($listType, $user) {
                            $query->select('member_id')
                                ->from('list_members')
                                ->where('list_type_id', $listType->id)
                                ->where('user_id', $user->id);
                        })->get();
                        break;
                }
            } else {
                Log::info('🔍 PROCESSING CUSTOM LIST', [
                    'user_id' => $user->id,
                    'list_id' => $listId
                ]);
                
                // Fetch members for custom list
                $members = User::whereIn('id', function ($query) use ($listId, $user) {
                    $query->select('member_id')
                        ->from('list_members')
                        ->where('list_id', $listId)
                        ->where('user_id', $user->id);
                })->get();
            }

            Log::info('🔍 FINAL LIST MEMBERS RESULT', [
                'user_id' => $user->id,
                'list_id' => $listId,
                'members_count' => $members->count(),
                'member_ids' => $members->pluck('id')->toArray()
            ]);

            return $members;
        } catch (QueryException $e) {
            Log::error('Failed to get list members', [
                'user_id' => $user->id,
                'list_id' => $listId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function createList(User $user, array $data): Lists
    {
        try {
            $list = new Lists([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'user_id' => $user->id
            ]);
            $list->save();
            
            Log::info('Created custom list', [
                'list_id' => $list->id,
                'user_id' => $user->id
            ]);
            
            return $list;
        } catch (QueryException $e) {
            Log::error('Failed to create list', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function addToList(User $user, $listIdOrType, User $member): void
    {
        try {
            if (is_numeric($listIdOrType)) {
                // Check if this is a system list type first
                $listType = ListType::find($listIdOrType);
                
                if ($listType && $listType->is_default) {
                    // It's a system list (like Muted Accounts, Blocked Accounts)
                    DB::table('list_members')->updateOrInsert(
                        [
                            'list_type_id' => $listType->id,
                            'user_id' => $user->id,
                            'member_id' => $member->id
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    Log::info('Added member to system list', [
                        'list_type_id' => $listType->id,
                        'list_type_name' => $listType->name,
                        'user_id' => $user->id,
                        'member_id' => $member->id
                    ]);
                } else {
                    // It's a custom list
                    DB::table('list_members')->updateOrInsert(
                        [
                            'list_id' => $listIdOrType,
                            'user_id' => $user->id,
                            'member_id' => $member->id
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    Log::info('Added member to custom list', [
                        'list_id' => $listIdOrType,
                        'user_id' => $user->id,
                        'member_id' => $member->id
                    ]);
                }
            } else {
                // Add to default list by name
                $listType = ListType::where('name', $listIdOrType)
                    ->where('is_default', true)
                    ->first();

                if (!$listType) {
                    throw new \InvalidArgumentException("Default list not found: {$listIdOrType}");
                }

                DB::table('list_members')->updateOrInsert(
                    [
                        'list_type_id' => $listType->id,
                        'user_id' => $user->id,
                        'member_id' => $member->id
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                Log::info('Added member to default list', [
                    'list_type' => $listType->name,
                    'user_id' => $user->id,
                    'member_id' => $member->id
                ]);
            }
        } catch (QueryException $e) {
            Log::error('Failed to add member to list', [
                'list_id_or_type' => $listIdOrType,
                'user_id' => $user->id,
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function removeFromList(User $user, $listIdOrType, User $member): array
    {
        Log::info('Attempting to remove member from list', [
            'list_id_or_type' => $listIdOrType,
            'user_id' => $user->id,
            'member_id' => $member->id
        ]);

        try {
            $removedCount = 0;
            $listName = '';
            $listType = null;

            // Check if it's a default list
            if (is_string($listIdOrType)) {
                $listType = ListType::where('name', $listIdOrType)->where('is_default', true)->first();
            } else {
                $listType = ListType::find($listIdOrType);
            }

            if ($listType) {
                // It's a default list
                $listName = $listType->name;

                // Prevent removal from specific lists
                if (in_array($listName, ['Followers', 'Following', 'Subscribed'])) {
                    return ['success' => false, 'message' => "Cannot remove members from the {$listName} list"];
                }

                $removedCount = DB::table('list_members')
                    ->where('list_type_id', $listType->id)
                    ->where('user_id', $user->id)
                    ->where('member_id', $member->id)
                    ->delete();
            } else {
                // It's a custom list
                $list = Lists::where('id', $listIdOrType)->where('user_id', $user->id)->first();
                if (!$list) {
                    return ['success' => false, 'message' => 'List not found'];
                }
                $listName = $list->name;
                $removedCount = DB::table('list_members')
                    ->where('list_id', $list->id)
                    ->where('user_id', $user->id)
                    ->where('member_id', $member->id)
                    ->delete();
            }

            if ($removedCount > 0) {
                Log::info('Successfully removed member from list', [
                    'list_name' => $listName,
                    'user_id' => $user->id,
                    'member_id' => $member->id
                ]);
                return ['success' => true, 'message' => 'Member removed successfully'];
            } else {
                Log::warning('Member not found in list', [
                    'list_name' => $listName,
                    'user_id' => $user->id,
                    'member_id' => $member->id
                ]);
                return ['success' => false, 'message' => "Member not found in the {$listName} list"];
            }
        } catch (QueryException $e) {
            Log::error('Failed to remove member from list', [
                'list_id_or_type' => $listIdOrType,
                'user_id' => $user->id,
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'An error occurred while removing the member'];
        }
    }

    public function deleteList(User $user, $listId): void
    {
        try {
            $list = Lists::where('id', $listId)
                ->where('user_id', $user->id)
                ->first();

            if ($list) {
                // Delete all members first
                DB::table('list_members')
                    ->where('list_id', $listId)
                    ->delete();
                
                // Then delete the list
                $list->delete();
                
                Log::info('Deleted custom list', [
                    'list_id' => $listId,
                    'user_id' => $user->id
                ]);
            } else {
                Log::warning('Attempted to delete non-existent or unauthorized list', [
                    'list_id' => $listId,
                    'user_id' => $user->id
                ]);
            }
        } catch (QueryException $e) {
            Log::error('Failed to delete list', [
                'list_id' => $listId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getDefaultList(User $user, string $listName)
    {
        try {
            $this->ensureUserHasDefaultLists($user);
            return ListType::where('name', $listName)
                ->where('is_default', true)
                ->first();
        } catch (QueryException $e) {
            Log::error('Failed to get default list', [
                'list_name' => $listName,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getListDetails(User $user, $listId): ?array
    {
        try {
            Log::info('Fetching list details', ['user_id' => $user->id, 'list_id' => $listId]);

            $listType = ListType::find($listId);
            if ($listType && $listType->is_default) {
                // Fetch details for default list
                $membersCount = DB::table('list_members')
                    ->where('list_type_id', $listId)
                    ->where('user_id', $user->id)
                    ->count();

                return [
                    'id' => $listType->id,
                    'name' => $listType->name,
                    'description' => $listType->description,
                    'is_default' => true,
                    'members_count' => $membersCount
                ];
            } else {
                // Fetch details for custom list
                $list = Lists::where('id', $listId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($list) {
                    $membersCount = $list->members()->count();

                    return [
                        'id' => $list->id,
                        'name' => $list->name,
                        'description' => $list->description,
                        'is_default' => false,
                        'members_count' => $membersCount
                    ];
                }
            }

            Log::warning('List not found', ['list_id' => $listId, 'user_id' => $user->id]);
            return null;
        } catch (QueryException $e) {
            Log::error('Failed to get list details', [
                'user_id' => $user->id,
                'list_id' => $listId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}