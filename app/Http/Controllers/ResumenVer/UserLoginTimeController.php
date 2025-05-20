<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\UserLoginSession;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserLoginTimeController extends Controller
{
    /**
     * Records a user's login time
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordLogin(Request $request)
    {
        $userId = Auth::id();
        
        // Record login time
        UserLoginSession::create([
            'user_id' => $userId,
            'login_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return response()->json(['message' => 'Login time recorded successfully']);
    }
    
    /**
     * Records a user's logout time
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordLogout(Request $request)
    {
        $userId = Auth::id();
        
        // Update the user's latest session record with logout time
        $session = UserLoginSession::where('user_id', $userId)
            ->whereNull('logout_time')
            ->latest('login_time')
            ->first();
            
        if ($session) {
            $session->update([
                'logout_time' => now(),
                'session_duration' => now()->diffInSeconds(Carbon::parse($session->login_time))
            ]);
        }
        
        return response()->json(['message' => 'Logout time recorded successfully']);
    }
    
    /**
     * Gets login time for a specific user
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserLoginTime($userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        $loginTime = UserLoginSession::where('user_id', $userId)
            ->latest('login_time')
            ->first();
        
        $user = User::find($userId);
        $userName = $user ? $user->name : 'Unknown';
        $userEmail = $user ? $user->email : 'Unknown';
            
        return response()->json([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'last_login_time' => $loginTime ? $loginTime->login_time->format('Y-m-d H:i:s') : null,
            'last_session_duration' => $loginTime ? $loginTime->formatted_duration : null
        ]);
    }
    
    /**
     * Gets daily login statistics for all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyLoginStats()
    {
        $stats = UserLoginSession::select(
            'user_login_sessions.user_id',
            'users.name as user_name',
            'users.email as user_email',
            DB::raw('DATE(login_time) as login_date'),
            DB::raw('COUNT(*) as login_count'),
            DB::raw('AVG(session_duration) as avg_session_duration'),
            DB::raw('SUM(session_duration) as total_session_duration')
        )
        ->join('users', 'users.id', '=', 'user_login_sessions.user_id')
        ->whereNotNull('logout_time')
        ->groupBy('user_login_sessions.user_id', 'users.name', 'users.email', 'login_date')
        ->orderBy('login_date', 'desc')
        ->get()
        ->map(function ($item) {
            $item->avg_session_duration = round($item->avg_session_duration);
            $item->avg_session_formatted = $this->formatDuration($item->avg_session_duration);
            $item->total_session_formatted = $this->formatDuration($item->total_session_duration);
            return $item;
        });
        
        return response()->json($stats);
    }
    
    /**
     * Gets weekly login statistics for all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeeklyLoginStats()
    {
        $stats = UserLoginSession::select(
            'user_login_sessions.user_id',
            'users.name as user_name',
            'users.email as user_email',
            DB::raw('YEARWEEK(login_time) as year_week'),
            DB::raw('MIN(DATE(login_time)) as week_start'),
            DB::raw('MAX(DATE(login_time)) as week_end'),
            DB::raw('COUNT(*) as login_count'),
            DB::raw('AVG(session_duration) as avg_session_duration'),
            DB::raw('SUM(session_duration) as total_session_duration')
        )
        ->join('users', 'users.id', '=', 'user_login_sessions.user_id')
        ->whereNotNull('logout_time')
        ->groupBy('user_login_sessions.user_id', 'users.name', 'users.email', 'year_week')
        ->orderBy('year_week', 'desc')
        ->get()
        ->map(function ($item) {
            $item->avg_session_duration = round($item->avg_session_duration);
            $item->avg_session_formatted = $this->formatDuration($item->avg_session_duration);
            $item->total_session_formatted = $this->formatDuration($item->total_session_duration);
            $item->week_range = "{$item->week_start} to {$item->week_end}";
            return $item;
        });
        
        return response()->json($stats);
    }
    
    /**
     * Gets monthly login statistics for all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyLoginStats()
    {
        $stats = UserLoginSession::select(
            'user_login_sessions.user_id',
            'users.name as user_name',
            'users.email as user_email',
            DB::raw('YEAR(login_time) as year'),
            DB::raw('MONTH(login_time) as month'),
            DB::raw("CONCAT(YEAR(login_time), '-', LPAD(MONTH(login_time), 2, '0')) as year_month"),
            DB::raw('COUNT(*) as login_count'),
            DB::raw('AVG(session_duration) as avg_session_duration'),
            DB::raw('SUM(session_duration) as total_session_duration')
        )
        ->join('users', 'users.id', '=', 'user_login_sessions.user_id')
        ->whereNotNull('logout_time')
        ->groupBy('user_login_sessions.user_id', 'users.name', 'users.email', 'year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get()
        ->map(function ($item) {
            $item->avg_session_duration = round($item->avg_session_duration);
            $item->avg_session_formatted = $this->formatDuration($item->avg_session_duration);
            $item->total_session_formatted = $this->formatDuration($item->total_session_duration);
            $item->month_name = Carbon::createFromDate($item->year, $item->month, 1)->format('F Y');
            return $item;
        });
        
        return response()->json($stats);
    }
    
    /**
     * Generates a comprehensive login report for a date range
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoginReport(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,week,month',
            'user_id' => 'nullable|exists:users,id'
        ]);
        
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'day');
        $userId = $request->input('user_id');
        
        $query = UserLoginSession::join('users', 'users.id', '=', 'user_login_sessions.user_id')
            ->whereBetween('login_time', [$startDate, $endDate])
            ->whereNotNull('logout_time');
            
        if ($userId) {
            $query->where('user_login_sessions.user_id', $userId);
        }
            
        switch ($groupBy) {
            case 'week':
                $query->select(
                    'user_login_sessions.user_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    DB::raw('YEARWEEK(login_time) as time_period'),
                    DB::raw('MIN(DATE(login_time)) as period_start'),
                    DB::raw('MAX(DATE(login_time)) as period_end'),
                    DB::raw('COUNT(*) as login_count'),
                    DB::raw('AVG(session_duration) as avg_session_duration'),
                    DB::raw('SUM(session_duration) as total_session_duration')
                )
                ->groupBy('user_login_sessions.user_id', 'users.name', 'users.email', 'time_period');
                break;
                
            case 'month':
                $query->select(
                    'user_login_sessions.user_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    DB::raw('CONCAT(YEAR(login_time), "-", LPAD(MONTH(login_time), 2, "0")) as time_period'),
                    DB::raw('MIN(DATE(login_time)) as period_start'),
                    DB::raw('LAST_DAY(login_time) as period_end'),
                    DB::raw('COUNT(*) as login_count'),
                    DB::raw('AVG(session_duration) as avg_session_duration'),
                    DB::raw('SUM(session_duration) as total_session_duration')
                )
                ->groupBy('user_login_sessions.user_id', 'users.name', 'users.email', DB::raw('YEAR(login_time)'), DB::raw('MONTH(login_time)'));
                break;
                
            default:
                $query->select(
                    'user_login_sessions.user_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    DB::raw('DATE(login_time) as time_period'),
                    DB::raw('DATE(login_time) as period_start'),
                    DB::raw('DATE(login_time) as period_end'),
                    DB::raw('COUNT(*) as login_count'),
                    DB::raw('AVG(session_duration) as avg_session_duration'),
                    DB::raw('SUM(session_duration) as total_session_duration')
                )
                ->groupBy('user_login_sessions.user_id', 'users.name', 'users.email', DB::raw('DATE(login_time)'));
        }
        
        $stats = $query->orderBy('time_period', 'desc')->get()->map(function ($item) {
            $item->avg_session_duration = round($item->avg_session_duration);
            $item->avg_session_formatted = $this->formatDuration($item->avg_session_duration);
            $item->total_session_formatted = $this->formatDuration($item->total_session_duration);
            
            return $item;
        });
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy
            ],
            'data' => $stats
        ]);
    }

    /**
     * Format duration in seconds to human-readable format
     * 
     * @param int $seconds
     * @return string
     */
    private function formatDuration($seconds)
    {
        if (is_null($seconds)) {
            return 'N/A';
        }
        
        $seconds = (int)$seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
