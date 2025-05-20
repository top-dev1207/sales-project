<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjetivosVentas extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'objetivos_ventas';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year',
        'month',
        'monto',
        'descripcion',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'monto' => 'decimal:2',
    ];
    
    /**
     * Get monthly objectives for a specific year.
     *
     * @param int $year
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMonthlyObjectives($year)
    {
        return self::where('year', $year)
            ->where('month', '>', 0)
            ->orderBy('month')
            ->get();
    }
    
    /**
     * Get annual objective for a specific year.
     *
     * @param int $year
     * @return \App\Models\ObjetivosVentas|null
     */
    public static function getAnnualObjective($year)
    {
        return self::where('year', $year)
            ->where('month', 0)
            ->first();
    }
    
    /**
     * Calculate progress against objective for a specific period.
     *
     * @param int $year
     * @param int $month (0 for annual)
     * @param float $currentSales
     * @return array
     */
    public static function calculateProgress($year, $month, $currentSales)
    {
        $objective = self::where('year', $year)
            ->where('month', $month)
            ->first();
            
        if (!$objective) {
            // If no specific objective for this month, use annual objective divided by 12
            if ($month > 0) {
                $annualObjective = self::getAnnualObjective($year);
                $objectiveAmount = $annualObjective ? $annualObjective->monto / 12 : 0;
            } else {
                // If asking for annual objective but none exists, sum monthly objectives
                $objectiveAmount = self::getMonthlyObjectives($year)->sum('monto');
            }
        } else {
            $objectiveAmount = $objective->monto;
        }
        
        $progress = $objectiveAmount > 0 ? ($currentSales / $objectiveAmount) * 100 : 0;
        
        return [
            'objective_amount' => round($objectiveAmount, 2),
            'current_sales' => round($currentSales, 2),
            'progress_percentage' => round($progress, 2),
            'remaining_amount' => round($objectiveAmount - $currentSales, 2),
            'is_achieved' => $progress >= 100,
        ];
    }
}