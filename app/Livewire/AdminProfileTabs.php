<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
/*
 * Summary of AdminProfileTabs
 */
class AdminProfileTabs extends Component
{
    public $tab = null;
    public $tabname = 'personal_details';
    protected $queryString = ['tab'];
    public $name, $email, $username, $admin_id;
    public $current_password, $new_password, $new_password_confirmation;

    public function selectTab($tab){
        $this->tab = $tab;
    }

    public function mount(){
        $this->tab = request()->tab ? request()->tab : $this->tabname;

        if( Auth::guard('admin')->check() ){
            $admin = Admin::findOrFail(auth()->id());
            $this->admin_id = $admin->id;
            $this->name = $admin->name;
            $this->email = $admin->email;
            $this->username = $admin->username;
        }
    }

    public function updateAdminPersonalDetails(){
        $this->validate([
            'name'=>'required|min:5',
            'email'=>'required|email|unique:admins,email,'.$this->admin_id,
            'username'=>'required|min:3|unique:admins,username,'.$this->admin_id
        ]);

        Admin::find($this->admin_id)
            ->update([
                'name'=>$this->name,
                'email'=>$this->email,
                'username'=>$this->username
            ]);

        // $this->emit('updateAdminSellerHeaderInfo');
        // $this->dispatchBrowserEvent('updateAdminInfo',[
        //     'adminName'=>$this->name,
        //     'adminEmail'=>$this->email
        // ]);
        // $this->showToastr('success', 'Your personal details have been successfully updated.');
    }

    // public function showToastr($type, $message)
    // {
    // return $this->dispatchBrowserEvent('showToastr', [
    //     'type' => $type,
    //     'message' => $message
    // ]);
    // }

    public function updatePassword(){
        // dd('helo');
        $this->validate([
            'current_password'=>[
                'required', function($attribute, $value, $fail){
                    if(!Hash::check($value, Admin::find(auth('admin')->id())->password)){
                        return $fail(__('The current password is incorrect'));
                    }
                }
            ],
            'new_password'=>'required|min:5|max:45|confirmed'
        ]);

        // dd('VALIDATED!');

        $query = Admin::findOrFail(auth('admin')->id())->update([
            'password'=>Hash::make($this->new_password)
        ]);

        if($query){
            $this->current_password = $this->new_password = $this->new_password_confirmation = null;
            // $this->showToastr('success','Password successfull changed.');
        }else{
            // $this->showToastr('error','Something went wrong.');
        }
    }
    public function render()
    {
        return view('livewire.admin-profile-tabs');
    }
}
