<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\UserLoginSession;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserLoginAnalyticsController extends Controller
{
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            // Get page and per_page parameters from request
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20); // Default 20 items per page
            $search = $request->get('search', '');

            // Validate per_page limit (max 100 to prevent overload)
            $perPage = min($perPage, 100);

            // Build query
            $query = User::with(['area'])
                ->withTrashed(); // Include soft deleted users if needed

            // Add search functionality if search term provided
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // Add ordering
            $query->orderBy('created_at', 'desc');

            // Get paginated results
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform the data
            $transformedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'area_id' => $user->area_id,
                    'area_name' => $user->area ? $user->area->nombre : null,
                    'menuroles' => $user->menuroles,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at,
                    'roles' => $user->getRoleNames(), // Get user roles
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'has_more' => $users->hasMorePages(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getDailyLoginTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get daily login time data
        $dailyData = UserLoginSession::select(
            DB::raw('DATE(login_time) as date'),
            DB::raw('SUM(COALESCE(session_duration, 0)) as total_duration'),
            DB::raw('COUNT(*) as session_count'),
            DB::raw('AVG(COALESCE(session_duration, 0)) as avg_duration'),
            DB::raw('MAX(COALESCE(session_duration, 0)) as max_duration'),
            DB::raw('MIN(COALESCE(session_duration, 0)) as min_duration')
        )
            ->where('user_id', $userId)
            ->whereBetween('login_time', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(login_time)'))
            ->orderBy('date')
            ->get();

        // Format data for chart display
        $chartData = $dailyData->map(function ($item) {
            return [
                'date' => $item->date,
                'total_hours' => round($item->total_duration / 3600, 2), // Convert seconds to hours
                'total_minutes' => round($item->total_duration / 60, 2), // Convert seconds to minutes
                'session_count' => $item->session_count,
                'avg_hours' => round($item->avg_duration / 3600, 2),
                'max_hours' => round($item->max_duration / 3600, 2),
                'min_hours' => round($item->min_duration / 3600, 2),
            ];
        });

        // Calculate summary statistics
        $totalHours = $chartData->sum('total_hours');
        $avgDailyHours = $chartData->count() > 0 ? round($totalHours / $chartData->count(), 2) : 0;
        $totalSessions = $chartData->sum('session_count');

        return response()->json([
            'success' => true,
            'data' => [
                'chart_data' => $chartData,
                'summary' => [
                    'total_hours' => $totalHours,
                    'avg_daily_hours' => $avgDailyHours,
                    'total_sessions' => $totalSessions,
                    'days_analyzed' => $chartData->count(),
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString()
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get weekly login time analytics for a specific user and period
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeeklyLoginTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get weekly login time data
        $weeklyData = UserLoginSession::select(
            DB::raw('YEAR(login_time) as year'),
            DB::raw('WEEK(login_time, 1) as week'), // ISO week format
            DB::raw('DATE(DATE_SUB(login_time, INTERVAL WEEKDAY(login_time) DAY)) as week_start'),
            DB::raw('SUM(COALESCE(session_duration, 0)) as total_duration'),
            DB::raw('COUNT(*) as session_count'),
            DB::raw('AVG(COALESCE(session_duration, 0)) as avg_duration'),
            DB::raw('COUNT(DISTINCT DATE(login_time)) as active_days')
        )
            ->where('user_id', $userId)
            ->whereBetween('login_time', [$startDate, $endDate])
            ->groupBy('year', 'week', 'week_start')
            ->orderBy('year')
            ->orderBy('week')
            ->get();

        // Format data for chart display
        $chartData = $weeklyData->map(function ($item) {
            $weekStart = Carbon::parse($item->week_start);
            $weekEnd = $weekStart->copy()->addDays(6);

            return [
                'year' => $item->year,
                'week' => $item->week,
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'week_label' => "Week {$item->week} ({$weekStart->format('M d')} - {$weekEnd->format('M d')})",
                'total_hours' => round($item->total_duration / 3600, 2),
                'total_minutes' => round($item->total_duration / 60, 2),
                'session_count' => $item->session_count,
                'avg_hours' => round($item->avg_duration / 3600, 2),
                'active_days' => $item->active_days,
                'avg_hours_per_active_day' => $item->active_days > 0 ? round(($item->total_duration / 3600) / $item->active_days, 2) : 0
            ];
        });

        // Calculate summary statistics
        $totalHours = $chartData->sum('total_hours');
        $avgWeeklyHours = $chartData->count() > 0 ? round($totalHours / $chartData->count(), 2) : 0;
        $totalSessions = $chartData->sum('session_count');

        return response()->json([
            'success' => true,
            'data' => [
                'chart_data' => $chartData,
                'summary' => [
                    'total_hours' => $totalHours,
                    'avg_weekly_hours' => $avgWeeklyHours,
                    'total_sessions' => $totalSessions,
                    'weeks_analyzed' => $chartData->count(),
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString()
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get monthly login time analytics for a specific user and period
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyLoginTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get monthly login time data
        $monthlyData = UserLoginSession::select(
            DB::raw('YEAR(login_time) as year'),
            DB::raw('MONTH(login_time) as month'),
            DB::raw('DATE(DATE_FORMAT(login_time, "%Y-%m-01")) as month_start'),
            DB::raw('SUM(COALESCE(session_duration, 0)) as total_duration'),
            DB::raw('COUNT(*) as session_count'),
            DB::raw('AVG(COALESCE(session_duration, 0)) as avg_duration'),
            DB::raw('COUNT(DISTINCT DATE(login_time)) as active_days')
        )
            ->where('user_id', $userId)
            ->whereBetween('login_time', [$startDate, $endDate])
            ->groupBy('year', 'month', 'month_start')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format data for chart display
        $chartData = $monthlyData->map(function ($item) {
            $monthStart = Carbon::parse($item->month_start);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $daysInMonth = $monthStart->daysInMonth;

            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_start' => $monthStart->toDateString(),
                'month_end' => $monthEnd->toDateString(),
                'month_label' => $monthStart->format('F Y'),
                'month_short' => $monthStart->format('M Y'),
                'total_hours' => round($item->total_duration / 3600, 2),
                'total_minutes' => round($item->total_duration / 60, 2),
                'session_count' => $item->session_count,
                'avg_hours' => round($item->avg_duration / 3600, 2),
                'active_days' => $item->active_days,
                'days_in_month' => $daysInMonth,
                'activity_percentage' => round(($item->active_days / $daysInMonth) * 100, 2),
                'avg_hours_per_active_day' => $item->active_days > 0 ? round(($item->total_duration / 3600) / $item->active_days, 2) : 0
            ];
        });

        // Calculate summary statistics
        $totalHours = $chartData->sum('total_hours');
        $avgMonthlyHours = $chartData->count() > 0 ? round($totalHours / $chartData->count(), 2) : 0;
        $totalSessions = $chartData->sum('session_count');
        $avgActivityPercentage = $chartData->count() > 0 ? round($chartData->avg('activity_percentage'), 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'chart_data' => $chartData,
                'summary' => [
                    'total_hours' => $totalHours,
                    'avg_monthly_hours' => $avgMonthlyHours,
                    'total_sessions' => $totalSessions,
                    'months_analyzed' => $chartData->count(),
                    'avg_activity_percentage' => $avgActivityPercentage,
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString()
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get comprehensive analytics dashboard for a user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnalyticsDashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get user information
        $user = User::find($userId);

        // Get overall statistics
        $totalStats = UserLoginSession::where('user_id', $userId)
            ->whereBetween('login_time', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(COALESCE(session_duration, 0)) as total_duration,
                AVG(COALESCE(session_duration, 0)) as avg_duration,
                MAX(COALESCE(session_duration, 0)) as max_duration,
                MIN(COALESCE(session_duration, 0)) as min_duration,
                COUNT(DISTINCT DATE(login_time)) as active_days
            ')
            ->first();

        // Get peak activity hours
        $peakHours = UserLoginSession::where('user_id', $userId)
            ->whereBetween('login_time', [$startDate, $endDate])
            ->selectRaw('
                HOUR(login_time) as hour,
                COUNT(*) as login_count,
                SUM(COALESCE(session_duration, 0)) as total_duration
            ')
            ->groupBy('hour')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();

        // Get activity by day of week
        $dayOfWeekActivity = UserLoginSession::where('user_id', $userId)
            ->whereBetween('login_time', [$startDate, $endDate])
            ->selectRaw('
                DAYOFWEEK(login_time) as day_of_week,
                DAYNAME(login_time) as day_name,
                COUNT(*) as session_count,
                SUM(COALESCE(session_duration, 0)) as total_duration,
                AVG(COALESCE(session_duration, 0)) as avg_duration
            ')
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        // Calculate period length in days
        $periodDays = $startDate->diffInDays($endDate) + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'total_days' => $periodDays
                ],
                'overall_stats' => [
                    'total_sessions' => $totalStats->total_sessions ?? 0,
                    'total_hours' => round(($totalStats->total_duration ?? 0) / 3600, 2),
                    'avg_session_hours' => round(($totalStats->avg_duration ?? 0) / 3600, 2),
                    'max_session_hours' => round(($totalStats->max_duration ?? 0) / 3600, 2),
                    'min_session_hours' => round(($totalStats->min_duration ?? 0) / 3600, 2),
                    'active_days' => $totalStats->active_days ?? 0,
                    'activity_percentage' => $periodDays > 0 ? round((($totalStats->active_days ?? 0) / $periodDays) * 100, 2) : 0,
                    'avg_hours_per_day' => $periodDays > 0 ? round((($totalStats->total_duration ?? 0) / 3600) / $periodDays, 2) : 0
                ],
                'peak_hours' => $peakHours->map(function ($item) {
                    return [
                        'hour' => $item->hour,
                        'hour_label' => sprintf('%02d:00', $item->hour),
                        'login_count' => $item->login_count,
                        'total_hours' => round($item->total_duration / 3600, 2)
                    ];
                }),
                'day_of_week_activity' => $dayOfWeekActivity->map(function ($item) {
                    return [
                        'day_of_week' => $item->day_of_week,
                        'day_name' => $item->day_name,
                        'session_count' => $item->session_count,
                        'total_hours' => round($item->total_duration / 3600, 2),
                        'avg_hours' => round($item->avg_duration / 3600, 2)
                    ];
                })
            ]
        ]);
    }

    /**
     * Compare login time analytics between multiple users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:2|max:5',
            'user_ids.*' => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'required|in:daily,weekly,monthly'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userIds = $request->user_ids;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $groupBy = $request->group_by;

        // Get user information
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $comparisonData = [];

        foreach ($userIds as $userId) {
            $userData = $this->getUserDataByPeriod($userId, $startDate, $endDate, $groupBy);
            $comparisonData[] = [
                'user' => [
                    'id' => $userId,
                    'name' => $users[$userId]->name ?? 'Unknown',
                    'email' => $users[$userId]->email ?? 'Unknown'
                ],
                'data' => $userData['chart_data'],
                'summary' => $userData['summary']
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'comparison_data' => $comparisonData,
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'group_by' => $groupBy
                ]
            ]
        ]);
    }

    /**
     * Helper method to get user data by period type
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $groupBy
     * @return array
     */
    private function getUserDataByPeriod($userId, $startDate, $endDate, $groupBy)
    {
        switch ($groupBy) {
            case 'daily':
                $request = new Request([
                    'user_id' => $userId,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString()
                ]);
                $response = $this->getDailyLoginTime($request);
                break;
            case 'weekly':
                $request = new Request([
                    'user_id' => $userId,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString()
                ]);
                $response = $this->getWeeklyLoginTime($request);
                break;
            case 'monthly':
                $request = new Request([
                    'user_id' => $userId,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString()
                ]);
                $response = $this->getMonthlyLoginTime($request);
                break;
            default:
                return ['chart_data' => [], 'summary' => []];
        }

        $responseData = json_decode($response->getContent(), true);
        return $responseData['data'] ?? ['chart_data' => [], 'summary' => []];
    }
}
