<?php

use Illuminate\Database\Seeder;
use App\Models\Authorizations\Permission;
use App\Models\Locations\Country;
use App\Models\Authorizations\Role;

class MemberRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::where('name', 'ewallet.view')
            ->orWhere('name', 'ewallet.update')

            ->orWhere('name', 'ewallet.transaction.create')
            ->orWhere('name', 'ewallet.transaction.view')
            ->orWhere('name', 'ewallet.transaction.list')

            ->orWhere('name', 'esac.vouchers.view')
            ->orWhere('name', 'esac.vouchers.list')

            ->orWhere('name', 'esac.redemptions.view')
            ->orWhere('name', 'esac.redemptions.list')

            ->orWhere('name', 'esac.promotions.view')
            ->orWhere('name', 'esac.promotions.list')

            ->orWhere('name', 'esac.voucher.types.list')
            ->orWhere('name', 'esac.voucher.types.view')

            ->orWhere('name', 'esac.voucher.sub.types.view')
            ->orWhere('name', 'esac.voucher.sub.types.list')

            ->orWhere('name', 'campaigns.view')
            ->orWhere('name', 'campaigns.list')

            ->orWhere('name', 'members.view')
            ->orWhere('name', 'members.search')
            ->orWhere('name', 'members.update')

            ->orWhere('name', 'members.placement.tree.list')
            ->orWhere('name', 'members.sponsor.tree.list')

            ->orWhere('name', 'invoices.download')

            ->orWhere('name', 'sales.create')
            ->orWhere('name', 'sales.view')
            ->orWhere('name', 'sales.list')

            ->orWhere('name', 'locations.list')
            ->orWhere('name', 'locations.view')

            ->orWhere('name', 'kitting.view')
            ->orWhere('name', 'products.view')

            ->pluck('id');

        $countries = Country::active()->get();

        collect($countries)->each(function ($country) use ($permissions){
            $role = Role::updateOrCreate([
                'name' => strtoupper($country->code). '-' .'Member-Default',
                'label' => strtoupper($country->code). '-' .'Member-Default',
                'active' => 1
            ]);

            //attach role to the country
            $role->countries()->sync(['country_id' => $country->id]);

            //attach permissions to the role
            $role->permissions()->sync($permissions);
        });
    }
}
