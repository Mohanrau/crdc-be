<?php

use Illuminate\Database\Seeder;
use App\Models\Modules\Module;
use App\Models\Modules\Operation;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            //Settings module section---------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Settings',
                'name' => 'Settings',
                'alias' => 'settings',
                'description' => 'Settings Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'area'
                    ]
                ],
                'childs' => [
                    [
                        'parent_id' => 0,
                        'label' => 'Modules Setup',
                        'name' => 'modules.setup',
                        'alias' => 'modules',
                        'description' => 'Modules Setup Module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'create'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'update'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'update'
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'setup'
                            ],
                            [
                                'name' => 'delete',
                                'alias' => 'delete'
                            ]
                        ],
                    ],
                    [
                        'parent_id' => 0,
                        'label' => 'Role Groups',
                        'name' => 'role.groups',
                        'alias' => 'roles',
                        'description' => 'Role Groups Module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'group.add'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'group.update'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'group.update'
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'setup'
                            ]
                        ],
                    ],
                    [
                        'parent_id' => 0,
                        'label' => 'Roles',
                        'name' => 'roles',
                        'alias' => 'roles',
                        'description' => 'Roles Module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'role.builder'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'role.update'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'role.update'
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'setup'
                            ]
                        ],
                    ],
                    [
                        'parent_id' => 0,
                        'label' => 'Users',
                        'name' => 'users',
                        'alias' => 'user',
                        'description' => 'Users Module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'setup.add'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'setup.update'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'setup.update'
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'setup'
                            ],
                            [
                                'name' => 'search',
                                'alias' => 'search'
                            ]
                        ],
                    ]
                ]
            ],

            //products module section-----------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Products',
                'name' => 'products',
                'alias' => 'products.management',
                'description' => 'Products module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'standard.setup'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'standard.setup'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'standard.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Kitting module section------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Kitting',
                'name' => 'kitting',
                'alias' => 'products.management',
                'description' => 'kitting module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'kitting.setup'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'kitting.setup'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'kitting.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Product Grouping section----------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Products Grouping',
                'name' => 'products.grouping',
                'alias' => 'products.management',
                'description' => 'Product Grouping module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'grouping.setup'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'grouping.setup'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'grouping.setup'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'grouping.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Pwp & Foc section-----------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Pwp & Foc',
                'name' => 'pwpfoc',
                'alias' => 'products.management',
                'description' => 'Pwp & Foc module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'pwpfoc.setup'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'pwpfoc.setup'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'pwpfoc.setup'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'pwpfoc.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Locations module section----------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Locations',
                'name' => 'locations',
                'alias' => '',
                'description' => 'location module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => ''
                    ],
                    [
                        'name' => 'view',
                        'alias' => ''
                    ],
                    [
                        'name' => 'update',
                        'alias' => ''
                    ],
                    [
                        'name' => 'list',
                        'alias' => ''
                    ],
                    [
                        'name' => 'delete',
                        'alias' => ''
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //sales module section--------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Sales',
                'name' => 'sales',
                'alias' => 'sales.management',
                'description' => 'Sales module SOPM',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'sales.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
                'childs' => [
                    [
                        'label' => 'Sales Daily Report',
                        'name' => 'sales.report.daily',
                        'alias' => 'report',
                        'description' => 'Sales Daily Report',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'daily'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'daily'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Sales MPOS Report',
                        'name' => 'sales.mpos.report',
                        'alias' => 'report',
                        'description' => 'Sales MPOS Report',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'daily'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'daily'
                            ],
                        ],
                    ]
                ]
            ],

            //sales Rental section--------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Rental Sales',
                'name' => 'rental.sales',
                'alias' => 'sales.management',
                'description' => 'Rental Sales Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'rental.list'
                    ]
                ],
            ],

            //sales exchange module section-----------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Sales Exchange',
                'name' => 'sales.exchange',
                'alias' => 'sales.management',
                'description' => 'Sales Exchange module PE',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'exchange.new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'exchange.view'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'exchange.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],

                'childs' => [
                    [
                        'label' => 'Sales Exchange Credit Note',
                        'name' => 'sales.exchange.credit.note',
                        'alias' => 'sales.exchange',
                        'description' => 'Sales Exchange Credit Note',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'credit.note.download'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Sales Exchange Bill',
                        'name' => 'sales.exchange.bill',
                        'alias' => 'sales.exchange',
                        'description' => 'Sales Exchange Bill',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'bill.download'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Sales Exchange Legacy Sales',
                        'name' => 'sales.exchange.legacy.sales',
                        'alias' => 'sales.management.exchange.legacy',
                        'description' => 'Legacy Sales Exchange module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'new'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'view'
                            ],
                        ],
                    ]
                ],
            ],

            //sales cancellation module section-------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Sales Cancellation',
                'name' => 'sales.cancellation',
                'alias' => 'sales.management',
                'description' => 'Sales Cancellation module SC',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'cancellation.new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'cancellation.view'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'cancellation.view'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'cancellation.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],

                //credit note module & Legacy --------------------------------------------------------------------------
                'childs' => [
                    [
                        'label' => 'Sales Cancellation Credit Note',
                        'name' => 'sales.cancellation.credit.note',
                        'alias' => 'sales.cancellation',
                        'description' => 'Sales Cancellation Credit Note',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'credit.note.download'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Sales Cancellation Legacy Sales',
                        'name' => 'sales.cancellation.legacy.sales',
                        'alias' => 'sales.management.cancellation.legacy',
                        'description' => 'Legacy Sales Cancellation module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'new'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'view'
                            ],
                        ],
                    ]
                ],
            ],

            //Enrollment module-----------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Enrollments',
                'name' => 'enrollments',
                'alias' => 'sales.management',
                'description' => 'Enrollments Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'enrollment'
                    ],
                ],
            ],

            //invoice module -------------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Invoices',
                'name' => 'invoices',
                'alias' => 'invoices',
                'description' => 'Invoice view',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'download',
                        'alias' => 'download'
                    ],
                ],
            ],

            //Member module section-------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Members',
                'name' => 'members',
                'alias' => 'members.management',
                'description' => 'location module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'setup'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'setup'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'setup'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'personal.data.list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],

                'childs' => [
                    [
                        'label' => 'Member Placement Tree',
                        'name' => 'members.placement.tree',
                        'alias' => 'tree',
                        'description' => 'Member placement tree',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'list',
                                'alias' => 'placement'
                            ]
                        ],
                    ],

                    [
                        'label' => 'Member Sponsor Tree',
                        'name' => 'members.sponsor.tree',
                        'alias' => 'tree',
                        'description' => 'Member Sponsor Tree',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'list',
                                'alias' => 'sponsor'
                            ],
                        ],
                    ],

                    [
                        'label' => 'Member Rank',
                        'name' => 'members.rank',
                        'alias' => 'rank',
                        'description' => 'Member Rank Update',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'update'
                            ],
                        ],
                    ],

                    [
                        'label' => 'Member Reset Password',
                        'name' => 'members.reset.password',
                        'alias' => 'password',
                        'description' => 'Member Reset Password',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'update',
                                'alias' => 'reset'
                            ],
                        ],
                    ],

                    [
                        'label' => 'Member Status',
                        'name' => 'members.status',
                        'alias' => 'status',
                        'description' => 'Member Status',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'update'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Member Migration',
                        'name' => 'members.migration',
                        'alias' => 'status',
                        'description' => 'Member Migration',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'update'
                            ],
                        ],
                    ],
                ]
            ],

            //Bonus Management Module ----------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Bonus Management',
                'name' => 'bonus.management',
                'alias' => 'bonus.management',
                'description' => 'location module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'bonus.management'
                    ],
                ],
                'childs' => [
                    [
                        'label' => 'CW Bonus Income Statements',
                        'name' => 'cw.bonus.income.statements',
                        'alias' => 'income',
                        'description' => 'CW Bonus Income Statements Download',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'statement.download'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'statement'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Yearly Income Statements',
                        'name' => 'yearly.income.statements',
                        'alias' => 'yearly',
                        'description' => 'Yearly Income Statements Download',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'reports.download'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'reports'
                            ]
                        ],
                    ],
                    [
                        'label' => 'Stockist Commission Statements',
                        'name' => 'stockist.commission.statements',
                        'alias' => 'stockist.commission',
                        'description' => 'Stockist Commission Statements',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'reports.download'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'reports'
                            ]
                        ],
                    ]
                ]
            ],

            //Smart Library module section------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Smart Library',
                'name' => 'smart.library',
                'alias' => 'smart.library',
                'description' => 'Smart Library Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Campaign module section-----------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Campaigns',
                'name' => 'campaigns',
                'alias' => 'campaigns.management',
                'description' => 'Campaigns Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'campaign.create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'campaign.update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'campaign.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'campaign.list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'campaign.delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Esac Voucher Type module section--------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Esac Voucher Types',
                'name' => 'esac.voucher.types',
                'alias' => 'campaigns.management',
                'description' => 'Esac Voucher Types Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'esac.voucher.type.create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'esac.voucher.type.update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'esac.voucher.type.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'esac.voucher.type.list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'esac.voucher.type.delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Esac Voucher Sub Type module section----------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Esac Voucher Sub Types',
                'name' => 'esac.voucher.sub.types',
                'alias' => 'campaigns.management',
                'description' => 'Esac Voucher Sub Types Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'esac.voucher.type.sub.create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'esac.voucher.type.sub.update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'esac.voucher.type.sub.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'esac.voucher.type.sub.list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'esac.voucher.type.sub.delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Esac Voucher module section-------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Esac Vouchers',
                'name' => 'esac.vouchers',
                'alias' => 'campaigns.management',
                'description' => 'Esac Vouchers Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'esac.voucher.create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'esac.voucher.update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'esac.voucher.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'esac.voucher.list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'esac.voucher.delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Esac Promotion module section-----------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Esac Promotions',
                'name' => 'esac.promotions',
                'alias' => 'campaigns.management',
                'description' => 'Esac Promotions Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'esac.promotion.create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'esac.promotion.update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'esac.promotion.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'esac.promotion.list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'esac.promotion.delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Esac Redemption module section----------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Esac Redemptions',
                'name' => 'esac.redemptions',
                'alias' => 'campaigns.management',
                'description' => 'Esac Promotions Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'esac.redemption.create'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'esac.redemption.update'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'esac.redemption.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'esac.redemption.list'
                    ],
                    [
                        'name' => 'delete',
                        'alias' => 'esac.redemption.delete'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Stockist module section-----------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist',
                'name' => 'stockists',
                'alias' => 'stockist',
                'description' => 'Stockists Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'registration'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Stockist Consignment Deposit module ----------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Consignment Deposit',
                'name' => 'stockist.consignment.deposit',
                'alias' => 'stockist.funds',
                'description' => 'Stockist Consignment Deposit Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'detail'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'detail'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],

                'childs' => [
                    [
                        'label' => 'Consignment Deposit Files',
                        'name' => 'consignment.deposit.files',
                        'alias' => 'files',
                        'description' => 'Consignment Deposit Files',
                        'active' => 1,

                        'operations' => [
                            [
                                'name' => 'download',
                                'alias' => 'note.download'
                            ],
                        ],
                    ],
                ],
            ],

            //Stockist Consignment Return module -----------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Consignment Orders Return',
                'name' => 'stockist.consignment.return',
                'alias' => 'stockist.consignment.orders.return',
                'description' => 'Stockist Consignment Return Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'detail'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Stockist Consignment Sales module-------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Consignment Orders',
                'name' => 'stockist.consignment.orders',
                'alias' => 'stockist.consignment.orders.sales',
                'description' => 'Stockist Consignment Orders Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'create',
                        'alias' => 'new'
                    ],
                    [
                        'name' => 'view',
                        'alias' => 'detail'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],

            ],

            //Stockist Consignment Stock Report module------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Consignment Stock Report',
                'name' => 'stockist.consignment.stock.report',
                'alias' => 'stockist.consignment.stock.report',
                'description' => 'Stockist Consignment Stock Report',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'detail'
                    ]
                ],

            ],

            //Stockist Consignment Transaction module-------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Daily Transactions',
                'name' => 'stockist.daily.transactions',
                'alias' => 'stockist.transactions',
                'description' => 'Stockist Consignment Transactions Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'update',
                        'alias' => 'release.update'
                    ],
                    [
                        'name' => 'list',
                        'alias' => 'list'
                    ],
                    [
                        'name' => 'search',
                        'alias' => 'search'
                    ]
                ],
            ],

            //Stockist Daily module-------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Collection Report',
                'name' => 'stockist.collection.report',
                'alias' => 'stockist.collection.daily',
                'description' => 'Stockist Daily Collection Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'report'
                    ]
                ],
            ],

            //Stockist Payment Verification module----------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Stockist Payment Verifications',
                'name' => 'stockist.payment.verifications',
                'alias' => 'stockist',
                'description' => 'Stockist Payment Verifications Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'payment.verification'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'payment.verification.update'
                    ],
                ],
            ],

            //E-wallet module-------------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Ewallet',
                'name' => 'ewallet',
                'alias' => 'ewallet.management',
                'description' => 'Ewallet Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'view'
                    ],
                    [
                        'name' => 'update',
                        'alias' => 'update'
                    ]
                ],
                'childs' => [
                    [
                        'label' => 'Ewallet Transaction',
                        'name' => 'ewallet.transaction',
                        'alias' => 'ewallet.transaction',
                        'description' => 'Ewallet Transaction Module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'new'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'details'
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ]
                        ],
                    ],
                    [
                        'label' => 'Ewallet Adjustment',
                        'name' => 'adjustment',
                        'alias' => 'adjustment',
                        'description' => 'Ewallet Adjustment Module',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'new'
                            ],
                            [
                                'name' => 'view',
                                'alias' => 'details'
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'update'
                            ]
                        ],
                    ],
                    [
                        'label' => 'Giro Bank Payments',
                        'name' => 'giro.bank.payments',
                        'alias' => 'giro.payment',
                        'description' => 'Giro Bank Payments',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'create',
                                'alias' => 'generation'
                            ],
                            [
                                'name' => 'download',
                                'alias' => ''
                            ],
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ]
                        ],
                    ],
                    [
                        'label' => 'Giro Rejected Payments',
                        'name' => 'giro.rejected.payments',
                        'alias' => 'giro.payment.rejected',
                        'description' => 'Giro Rejected Payments',
                        'active' => 1,
                        'operations' => [
                            [
                                'name' => 'list',
                                'alias' => 'list'
                            ],
                            [
                                'name' => 'create',
                                'alias' => 'new'
                            ],
                            [
                                'name' => 'download',
                                'alias' => ''
                            ],
                            [
                                'name' => 'update',
                                'alias' => 'update'
                            ]
                        ],
                    ],
                ]
            ],

            //Dashboard module------------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Dashboard',
                'name' => 'dashboard',
                'alias' => 'dashboard',
                'description' => 'Dashboard Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'new'
                    ],
                ],
            ],

            //LoginAs module--------------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Login As',
                'name' => 'loginAs',
                'alias' => 'loginAs',
                'description' => 'LoginAs Module',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'setup.view'
                    ],
                ],
            ],

            //Procurement Module----------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Procurement Module',
                'name' => 'procurement.module',
                'alias' => 'procurement',
                'description' => 'Third Party Procurement',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'management'
                    ]
                ],
            ],

            //Inventory Module------------------------------------------------------------------------------------------
            [
                'parent_id' => 0,
                'label' => 'Inventory Module',
                'name' => 'inventory.module',
                'alias' => 'inventory',
                'description' => 'Third Party Inventory',
                'active' => 1,
                'operations' => [
                    [
                        'name' => 'view',
                        'alias' => 'management'
                    ]
                ],
            ],

        ];

        foreach ($data as $item) {
            $moduleOperationsIds = [];

            $moduleData = $item;

            unset($moduleData['operations']);

            if (isset($item['childs'])) {
                unset($moduleData['childs']);
            }

            $module = Module::updateOrCreate($moduleData);

            foreach ($item['operations'] as $operation) {
                $operationData = Operation::where('name', $operation['name'])->first();

                $module->permissions()->updateOrCreate([
                    'operation_id' => $operationData->id,
                    'name' => $item['name'] . '.' . $operation['name'],
                    'label' => $operation['name'] . ' ' . $item['name'],
                    'alias' => $operation['alias']
                ]);

                $moduleOperationsIds[] = $operationData->id;
            }

            //check for the childs
            if (isset($item['childs'])) {

                foreach ($item['childs'] as $child) {
                    $subModuleOperationsIds = [];

                    $subModuleData = $child;

                    unset($subModuleData['operations']);

                    if (isset($child['childs'])) {
                        unset($subModuleData['childs']);
                    }

                    $subModule = Module::updateOrCreate(array_merge(
                            $subModuleData,
                            ['parent_id' => $module->id]
                        )
                    );

                    foreach ($child['operations'] as $subOperation) {
                        $subOperationData = Operation::where('name', $subOperation['name'])->first();

                        $subModule->permissions()->updateOrCreate([
                            'operation_id' => $subOperationData->id,
                            'name' => $child['name'] . '.' . $subOperation['name'],
                            'label' => $subOperation['name'],
                            'alias' => $module->alias . '.' . $subModule->alias . '.' . $subOperation['alias']
                        ]);

                        $subModuleOperationsIds[] = $subOperationData->id;
                    }

                    //attach operations to modules
                    $subModule->operations()->sync($subModuleOperationsIds);
                }
            }

            //attach operations to modules
            $module->operations()->sync($moduleOperationsIds);
        }
    }
}
