<?php

namespace App;

use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class Customer extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Notifiable, SoftDeletes, HasApiTokens;
    use Authenticatable;
    use CanResetPassword;


    protected $guard = 'customer';

    protected $fillable = [
        'name', 'email', 'password'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function slave_customers() {
        return $this->hasMany(SlaveCustomer::class, 'customer_id');
    }

    public function customer_tools() {
        return $this->hasMany(CustomerTool::class, 'customer_id');
    }

    public function getWorkers() {
        return $this->whereIn('id', $this->find(auth()->guard('customer')->user()->id)->slave_customers->pluck('slave_id'))->get();
    }

    public function getProtokols($customer_id=0)
    {
        $data = [];
        if ($customer_id!=0) {
            $data =  $this->find($customer_id)->protokols;
        }
        return json_encode(['data' => $data, 'customer_id' => $customer_id]);
    }

    public function protokols() {
        return $this->hasMany(Protokol::class);
    }

    public function new_protokols() {
        return $this->hasMany(Protokol::class)->where('protokols.exported', '=', '0');
    }

    public function getDataChart($id) {
//        $id = auth()->guard('customer')->user()->id;
        $quest  = Customer::join('protokols','customers.id','protokols.customer_id')
            ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m") as date, count(protokols.protokol_num) count'))
            ->whereRaw('date_format(protokol_dt, "%Y-%m") <> "0000-00"')
            ->where('customer_id', $id)
            ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m")'))
            ->get();

        $labels = [];
        $data = [];
        $labels[] = '2018-06';
        $data[] = 0;
        foreach ($quest as $item) {
            $labels[] = $item->date;
            $data[] = $item->count;
        }
//
//        return json_encode($quest);

        return [
            'labels' => $labels,
            'datasets' => array([
                'label' => 'Поверки за период (кол-во по месяцам)',
                'backgroundColor' => '#3490dc',
                'data' => $data,
            ])
        ];

    }

    public function getDataReportDays($id,$start,$end)
    {
        $labels = [];
        $data = [];
//        $labels[] = '2018-06';
//        $data[] = 0;
//        $all = collect([]);
//        $days = \DB::select('call month_days(now())');
//        $days = collect($days);

        if ($start and $end) {

            $quest = Customer::join('protokols', 'customers.id', 'protokols.customer_id')
                ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m-%d") as date, count(protokols.protokol_num) count'))
                ->where('protokol_dt', '>=', $start)
                ->where('protokol_dt', '<=', $end)
                ->where('customer_id', $id)
                ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m-%d")'))
                ->get();


            $curr_date = $start;
            foreach ($quest as $item) {
                while ($curr_date < $item->date) {
                    if($curr_date != $item->date) {
                        $labels[] = $curr_date;
                        $data[] = 0;
                    }
                    $curr_date = date_create($curr_date);
                    date_add($curr_date, date_interval_create_from_date_string('1 days'));
                    $curr_date = date_format($curr_date, 'Y-m-d');
                }
                $labels[] = $item->date;
                $data[] = $item->count;
                $curr_date = $item->date;
                $curr_date = date_create($curr_date);
                date_add($curr_date, date_interval_create_from_date_string('1 days'));
                $curr_date = date_format($curr_date, 'Y-m-d');
            }
        }

        return [
            'labels' => $labels,
            'datasets' => array([
                'label' => 'Поверки за период (кол-во по дням)',
                'backgroundColor' => '#3490dc',
                'data' => $data,
            ])
        ];

    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
