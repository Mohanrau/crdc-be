<?php

use Illuminate\Database\Seeder;
use App\Models\Authorizations\Permission;
use App\Models\Locations\Country;
use App\Models\Authorizations\Role;

class StockistRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::where('name', 'dashboard.view')

            ->orWhere('name', 'stockist.daily.transactions.update')
            ->orWhere('name', 'stockist.daily.transactions.list')
            ->orWhere('name', 'stockist.daily.transactions.search')

            ->orWhere('name', 'stockist.consignment.stock.report.view')

            ->orWhere('name', 'stockist.consignment.orders.create')
            ->orWhere('name', 'stockist.consignment.orders.view')
            ->orWhere('name', 'stockist.consignment.orders.list')
            ->orWhere('name', 'stockist.consignment.orders.search')

            ->orWhere('name', 'stockist.consignment.return.create')
            ->orWhere('name', 'stockist.consignment.return.view')
            ->orWhere('name', 'stockist.consignment.return.list')
            ->orWhere('name', 'stockist.consignment.return.search')

            ->orWhere('name', 'stockist.consignment.deposit.create')
            ->orWhere('name', 'stockist.consignment.deposit.update')
            ->orWhere('name', 'stockist.consignment.deposit.view')
            ->orWhere('name', 'stockist.consignment.deposit.list')
            ->orWhere('name', 'stockist.consignment.deposit.search')

            ->orWhere('name', 'stockists.search')

            ->orWhere('name', 'esac.redemptions.create')
            ->orWhere('name', 'esac.redemptions.update')
            ->orWhere('name', 'esac.redemptions.view')
            ->orWhere('name', 'esac.redemptions.list')
            ->orWhere('name', 'esac.redemptions.search')

            ->orWhere('name', 'esac.promotions.view')
            ->orWhere('name', 'esac.promotions.list')

            ->orWhere('name', 'esac.voucher.types.list')
            ->orWhere('name', 'esac.voucher.types.view')

            ->orWhere('name', 'esac.voucher.sub.types.view')
            ->orWhere('name', 'esac.voucher.sub.types.list')

            ->orWhere('name', 'campaigns.view')
            ->orWhere('name', 'campaigns.list')

            ->orWhere('name', 'members.search')

            ->orWhere('name', 'members.placement.tree.list')
            ->orWhere('name', 'members.sponsor.tree.list')

            ->orWhere('name', 'invoices.download')

            ->orWhere('name', 'sales.create')
            ->orWhere('name', 'sales.update')
            ->orWhere('name', 'sales.view')
            ->orWhere('name', 'sales.list')
            ->orWhere('name', 'sales.search')

            ->orWhere('name', 'locations.create')
            ->orWhere('name', 'locations.update')
            ->orWhere('name', 'locations.list')
            ->orWhere('name', 'locations.view')

            ->orWhere('name', 'kitting.view')
            ->orWhere('name', 'products.view')

            ->pluck('id');

        $countries = Country::active()->get();

        collect($countries)->each(function ($country) use ($permissions){
            $role = Role::updateOrCreate([
                'name' => strtoupper($country->code). '-' .'Stockist-Default',
                'label' => strtoupper($country->code). '-' .'Stockist-Default',
                'active' => 1
            ]);

            //attach role to the country
            $role->countries()->sync(['country_id' => $country->id]);

            //attach permissions to the role
            $role->permissions()->sync($permissions);
        });
    }
}
