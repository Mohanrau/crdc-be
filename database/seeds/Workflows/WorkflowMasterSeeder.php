<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Workflows\WorkflowMaster,
    Workflows\WorkflowMasterStep
};


class WorkflowMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $master = [
            [
                'id'=> 1,
                'name' => 'Same Day Cancellation',
                'code' => 'SC001',
                'active' => 1
            ],
            [
                'id'=> 2,
                'name' => 'Within Cooling Period',
                'code' => 'SC002',
                'active' => 1
            ],
            [
                'id'=> 3,
                'name' => 'Buy Back Policy',
                'code' => 'SC003',
                'active' => 1
            ],
            [
                'id'=> 4,
                'name' => 'Consignment Deposit',
                'code' => 'CONSIGNMENT_DEPOSIT',
                'active' => 1
            ],
            [
                'id'=> 5,
                'name' => 'Consignment Refund',
                'code' => 'CONSIGNMENT_REFUND',
                'active' => 1
            ],
            [
                'id'=> 6,
                'name' => 'Consignment Order',
                'code' => 'CONSIGNMENT_ORDER',
                'active' => 1
            ],
            [
                'id'=> 7,
                'name' => 'Consignment Return',
                'code' => 'CONSIGNMENT_RETURN',
                'active' => 1
            ],
            [
                'id'=> 8,
                'name' => 'Same Day Cancellation With Payment Gateway',
                'code' => 'SC004',
                'active' => 1
            ],
            [
                'id'=> 9,
                'name' => 'Rental Sale Order',
                'code' => 'RENTAL_SALE_ORDER',
                'active' => 1
            ]
        ];

        foreach ($master as $item) {

            WorkflowMaster::updateOrCreate($item);

        }

        $step = [
            [
                "master_id"=> 1,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Generate CN",
                "last_step" => 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Generate CN","trigger":"generateCnWithCompleteStatus"}]}}'
            ],
            [
                "master_id"=> 1,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id" => 2,
                "sequence" => 1,
                "step"=> 10,
                "name"=> "CS Mngr Approval",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"buy_back_amount_editable":true,"show_child":false,"fields":[{"type":"button","label":"Approve","trigger":"sendApproval","require_confirmation":true,"conformation_message":"Confirm approval?","share":{"pos":"C"},"buy_back_amount":0},{"type":"button","label":"Decline","trigger":"toggleChildVisibility","share":{"pos":"C"}},{"type":"","share":{"pos":"C"}}],"child":{"show_child":false,"fields":[{"type":"textarea","label":"RejectionReason","trigger":"updateRejectionReason","required":true,"value":"","max_height":50,"min_rows":3,"share":{"pos":"P"}},{"type":"button","label":"Save","trigger":"sendDecline","end_workflow":true,"rejection_reason":"","is_rejection_step":true,"require_confirmation":true,"conformation_message":"Confirm decline?","share":{"pos":"C"}}]}}}'
            ],
            [
                "master_id"=> 2,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Generate CN",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Generate CN","trigger":"generateCN","require_confirmation":true,"conformation_message":"Confirm generate credit note?"}]}}'
            ],
            [
                "master_id"=> 2,
                "sequence"=> 3,
                "step"=> 30,
                "name"=> "Pending Refund",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Process Refund","trigger":"processRefund","require_confirmation":true,"conformation_message":"Confirm refund?"}]}}'
            ],
            [
                "master_id"=> 2,
                "sequence"=> 4,
                "step"=> 40,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 3,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "CS Mngr Approval",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"buy_back_amount_editable":true,"show_child":false,"fields":[{"type":"button","label":"Approve","trigger":"sendApproval","require_confirmation":true,"conformation_message":"Confirm approval?","share":{"pos":"C"},"buy_back_amount":0},{"type":"button","label":"Decline","trigger":"toggleChildVisibility","share":{"pos":"C"}},{"type":"","share":{"pos":"C"}}],"child":{"show_child":false,"fields":[{"type":"textarea","label":"RejectionReason","trigger":"updateRejectionReason","required":true,"value":"","max_height":50,"min_rows":3,"share":{"pos":"P"}},{"type":"button","label":"Save","trigger":"sendDecline","end_workflow":true,"rejection_reason":"","is_rejection_step":true,"require_confirmation":true,"conformation_message":"Confirm decline?","share":{"pos":"C"}}]}}}'
            ],
            [
                "master_id"=> 3,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Generate CN",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Generate CN","trigger":"generateCN","require_confirmation":true,"conformation_message":"Confirm generate credit note?"}]}}'
            ],
            [
                "master_id"=> 3,
                "sequence"=> 3,
                "step"=> 30,
                "name"=> "Pending Refund",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Process Refund","trigger":"processRefund","require_confirmation":true,"conformation_message":"Confirm refund?"}]}}'
            ],
            [
                "master_id"=> 3,
                "sequence"=> 4,
                "step"=> 40,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 4,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Approval Action",
                "last_step" => 0,
                "step_data" => '{"eventClass": "App\\\Events\\\Stockists\\\ConsignmentDepositRefundEvents","actions": {"show_child": false,"fields": [{"type": "button","label": "Approve","trigger": "sendDepositApproval","require_confirmation": true,"conformation_message": "Confirm approval?","share": {"pos": "C"}}, {"type": "button","label": "Decline","trigger": "toggleChildVisibility","share": {"pos": "C"}},{"type": "","share": {"pos": "C"}}],"child": {"show_child": false,"fields": [{"type": "textarea","label": "RejectionReason","trigger": "updateRejectionReason","required": true,"value": "","max_height": 50,"min_rows": 3,"share": {"pos": "P"}},{"type": "button","label": "Save","trigger": "sendDepositDecline","end_workflow": true,"rejection_reason": "","is_rejection_step": true,"require_confirmation": true,"conformation_message": "Confirm decline?","share": {"pos": "C"}}]}}}'
            ],
            [
                "master_id"=> 4,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 5,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Verification Action",
                "last_step" => 0,
                "step_data" => '{"eventClass": "App\\\Events\\\Stockists\\\ConsignmentDepositRefundEvents","actions": {"show_child": false,"fields": [{"type": "button","label": "Approve","trigger": "sendRefundVerified","require_confirmation": true,"conformation_message": "Confirm verified?","share": {"pos": "C"}},{"type": "button","label": "Decline","trigger": "toggleChildVisibility","share": {"pos": "C"}},{"type": "","share": {"pos": "C"}}],"child": {"show_child": false,"fields": [{"type": "textarea","label": "RejectionReason","trigger": "updateRejectionReason","required": true,"value": "","max_height": 50,"min_rows": 3,"share": {"pos": "P"}},{"type": "button","label": "Save","trigger": "sendRefundRejected","end_workflow": true,"rejection_reason": "","is_rejection_step": true,"require_confirmation": true,"conformation_message": "Confirm reject?","share": {"pos": "C"}}]}}}'
            ],
            [
                "master_id"=> 5,
                "sequence"=> 2,
                "step"=> 10,
                "name"=> "Approval Action",
                "last_step" => 0,
                "step_data" => '{"eventClass": "App\\\Events\\\Stockists\\\ConsignmentDepositRefundEvents","actions": {"show_child": false,"fields": [{"type": "button","label": "Approve","trigger": "sendRefundApproval","require_confirmation": true,"conformation_message": "Confirm approval?","share": {"pos": "C"}},{"type": "button","label": "Decline","trigger": "toggleChildVisibility","share": {"pos": "C"}},{"type": "","share": {"pos": "C"}}],"child": {"show_child": false,"fields": [{"type": "textarea","label": "RejectionReason","trigger": "updateRejectionReason","required": true,"value": "","max_height": 50,"min_rows": 3,"share": {"pos": "P"}},{"type": "button","label": "Save","trigger": "sendRefundDecline","end_workflow": true,"rejection_reason": "","is_rejection_step": true,"require_confirmation": true,"conformation_message": "Confirm decline?","share": {"pos": "C"}}]}}}'
            ],
            [
                "master_id"=> 5,
                "sequence"=> 3,
                "step"=> 20,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 6,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Approval Action",
                "last_step" => 0,
                "step_data" => '{"eventClass": "App\\\Events\\\Stockists\\\ConsignmentOrderReturnEvents","actions": {"show_child": false,"fields": [{"type": "button","label": "Approve","trigger": "sendOrderApproval","require_confirmation": true,"conformation_message": "Confirm approval?","share": {"pos": "C"}},{"type": "button","label": "Decline","trigger": "toggleChildVisibility","share": {"pos": "C"}},{"type": "","share": {"pos": "C"}}],"child": {"show_child": false,"fields": [{"type": "textarea","label": "RejectionReason","trigger": "updateRejectionReason","required": true,"value": "","max_height": 50,"min_rows": 3,"share": {"pos": "P"}},{"type": "button","label": "Save","trigger": "sendOrderDecline","end_workflow": true,"rejection_reason": "","is_rejection_step": true,"require_confirmation": true,"conformation_message": "Confirm decline?","share": {"pos": "C"}}]}}}'
            ],
            [
                "master_id"=> 6,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 7,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Verification Action",
                "last_step" => 0,
                "step_data" => '{"eventClass": "App\\\Events\\\Stockists\\\ConsignmentOrderReturnEvents","actions": {"show_child": false,"fields": [{"type": "button","label": "Approve","trigger": "sendReturnVerified","require_confirmation": true,"conformation_message": "Confirm verified?","share": {"pos": "C"}},{"type": "button","label": "Decline","trigger": "toggleChildVisibility","share": {"pos": "C"}},{"type": "","share": {"pos": "C"}}],"child": {"show_child": false,"fields": [{"type": "textarea","label": "RejectionReason","trigger": "updateRejectionReason","required": true,"value": "","max_height": 50,"min_rows": 3,"share": {"pos": "P"}},{"type": "button","label": "Save","trigger": "sendReturnDecline","end_workflow": true,"rejection_reason": "","is_rejection_step": true,"require_confirmation": true,"conformation_message": "Confirm decline?","share": {"pos": "C"}}]}}}'
            ],
            [
                "master_id"=> 7,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 8,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Generate CN",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Generate CN","trigger":"sameDayGenerateCnWithPendingRefundStatus"}]}}'
            ],
            [
                "master_id"=> 8,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Pending Refund",
                "last_step"=> 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\SaleCancellationEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Process Refund","trigger":"processRefund","require_confirmation":true,"conformation_message":"Confirm refund?"}]}}'
            ],
            [
                "master_id"=> 8,
                "sequence"=> 3,
                "step"=> 30,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ],
            [
                "master_id"=> 9,
                "sequence"=> 1,
                "step"=> 10,
                "name"=> "Stock Release",
                "last_step" => 0,
                "step_data" => '{"eventClass":"App\\\Events\\\Sales\\\RentalSaleOrderEvents","actions":{"show_child":false,"fields":[{"type":"button","label":"Process Stock Release","trigger":"processRelease","require_confirmation":true,"conformation_message":"Confirm release?"}]}}'
            ],
            [
                "master_id"=> 9,
                "sequence"=> 2,
                "step"=> 20,
                "name"=> "Complete",
                "last_step"=> 1,
                "step_data" => '{"actions":{"show_child":false,"fields":[{"type":"complete"}]}}'
            ]
        ];

        foreach ($step as $item) {

            WorkflowMasterStep::updateOrCreate($item);

        }
    }
}
