<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRole;
use App\VpnUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class VpnController extends Controller
{
    public function __construct()
    {
        $this->middleware(CheckRole::class);
    }
    public function index(){
        $vpns = VpnUser::all();
        return view('vpnall',['vpn'=>$vpns]);
    }
    public function save(Request $r){
        echo $r;
        $this->saveconf(VpnUser::all());

    }
    public function edit(Request $r, $id = ""){
        $freename = config('app.freename');
        if ($r->method() == 'POST'){
            if(!$r->fname){
                $fname = $freename;
            }
            else {
                $fname = $r->fname;
            }
            $vpns = VpnUser::all();

            if($r->id){
                $existent_users = $vpns->where('username','=',$r->login);
                $existent_ips = $vpns->where('ip','=',$r->ip);
                if($existent_users->count()>1){
                    $c = $existent_users->first();
                    return "Cant create vpn user, login already exists in $c";

                }
                if($existent_ips->count()>1){
                    $c = $existent_users->first();
                    return "Cant create vpn user, ip already exists $c";
                }
                $vpn = VpnUser::find($r->id);
                $vpn->username = $r->login;
                $vpn->password = $r->pwd;
                $vpn->fullname = $fname;
                $vpn->ip = $r->ip;
                $vpn->comment = $r->comment;
                $vpn->server = "*";
                $vpn->save();
            }
            else {
                $existent_users = $vpns->where('username','=',$r->login);
                $existent_ips = $vpns->where('ip','=',$r->ip);
                if($existent_users->count()){
                    $c = $existent_users->first();
                    return "Cant create vpn user, login already exists in $c";

                }
                if($existent_ips->count()){
                    $c = $existent_users->first();
                    return "Cant create vpn user, ip already exists $c";
                }
                $vpn = new VpnUser();
                $vpn->username = $r->login;
                $vpn->password = $r->pwd;
                $vpn->fullname = $fname;
                $vpn->ip = $r->ip;
                $vpn->comment = $r->comment;
                $vpn->server = "*";
                $vpn->save();
            }
            $vpns = VpnUser::all();
            $this->saveconf($vpns);
            return redirect()->action('VpnController@index');
        }
        if(!$id){
            return view('editvpn');
        }
        $vpn = VpnUser::find($id);
        return view('editvpn',['vpn'=>$vpn,'freename'=>$freename]);

    }
    private function saveconf(Collection $vpns){
        $fh = fopen('../chap_secrets.conf','w');
        foreach ($vpns as $vpn) {
            $str = "$vpn->username $vpn->server $vpn->password $vpn->ip \n";
            fputs($fh,$str);

        }
        fclose($fh);
        exec('sudo /bin/systemctl restart pptpd');
        exec('sudo /bin/systemctl restart x2ltpd');
    }
}
