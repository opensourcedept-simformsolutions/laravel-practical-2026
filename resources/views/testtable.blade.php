@extends('layouts.app')

@section('title', 'Table Examples')

@section('content')
    <div class="form-contained" style="max-width: 1100px;">
        <x-form.form-section title="DataTable Examples" description="Reference examples for the reusable x-table component.">
                <x-table id="visitorsTable">
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th>Phone</th>
                            <th>Host</th>
                            <th>Status</th>
                            <th>Visit Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Rahul Sharma</td>
                            <td>9876543210</td>
                            <td>Amit Patel</td>
                            <td>Approved</td>
                            <td>10:15 AM</td>
                        </tr>
                        <tr>
                            <td>Priya Mehta</td>
                            <td>9988776655</td>
                            <td>Neha Shah</td>
                            <td>Pending</td>
                            <td>11:00 AM</td>
                        </tr>
                        <tr>
                            <td>Arjun Nair</td>
                            <td>9123456780</td>
                            <td>Ravi Kumar</td>
                            <td>Checked In</td>
                            <td>11:40 AM</td>
                        </tr>
                        <tr>
                            <td>Sneha Joshi</td>
                            <td>9012345678</td>
                            <td>Vikas Gupta</td>
                            <td>Completed</td>
                            <td>01:10 PM</td>
                        </tr>
<<<<<<< HEAD
                    </tbody>
                </x-table>
            </x-form.fieldset>

            <x-form.fieldset legend="Search Disabled">
                <x-table id="deliveriesTable" :searching="false" :page-length="5">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Courier</th>
                            <th>Flat</th>
                            <th>Received By</th>
                            <th>State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>DEL-1001</td>
                            <td>Bluedart</td>
                            <td>A-102</td>
                            <td>Security Desk</td>
                            <td>Awaiting Pickup</td>
                        </tr>
                        <tr>
                            <td>DEL-1002</td>
                            <td>DTDC</td>
                            <td>B-304</td>
                            <td>Gatekeeper</td>
                            <td>Delivered</td>
                        </tr>
                        <tr>
                            <td>DEL-1003</td>
                            <td>Amazon</td>
                            <td>C-210</td>
                            <td>Security Desk</td>
                            <td>Awaiting Pickup</td>
                        </tr>
                        <tr>
                            <td>DEL-1004</td>
                            <td>Flipkart</td>
                            <td>D-508</td>
                            <td>Resident</td>
                            <td>Delivered</td>
                        </tr>
                        <tr>
                            <td>DEL-1005</td>
                            <td>Delhivery</td>
                            <td>A-401</td>
                            <td>Security Desk</td>
                            <td>Returned</td>
                        </tr>
                    </tbody>
                </x-table>
            </x-form.fieldset>

            <x-form.fieldset legend="Paging Disabled">
                <x-table id="complaintsTable" :paging="false" :ordering="true">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Resident</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CMP-301</td>
                            <td>Anita Rao</td>
                            <td>Water Supply</td>
                            <td>High</td>
                            <td>Open</td>
                        </tr>
                        <tr>
                            <td>CMP-302</td>
                            <td>Sameer Khan</td>
                            <td>Parking</td>
                            <td>Medium</td>
                            <td>In Progress</td>
                        </tr>
                        <tr>
                            <td>CMP-303</td>
                            <td>Ritu Verma</td>
                            <td>Electricity</td>
                            <td>High</td>
                            <td>Resolved</td>
                        </tr>
                    </tbody>
                </x-table>
            </x-form.fieldset>
=======
                        <tr>
                            <td>Rahul Sharma</td>
                            <td>9876543210</td>
                            <td>Amit Patel</td>
                            <td>Approved</td>
                            <td>10:15 AM</td>
                        </tr>
                        <tr>
                            <td>Priya Mehta</td>
                            <td>9988776655</td>
                            <td>Neha Shah</td>
                            <td>Pending</td>
                            <td>11:00 AM</td>
                        </tr>
                        <tr>
                            <td>Arjun Nair</td>
                            <td>9123456780</td>
                            <td>Ravi Kumar</td>
                            <td>Checked In</td>
                            <td>11:40 AM</td>
                        </tr>
                        <tr>
                            <td>Sneha Joshi</td>
                            <td>9012345678</td>
                            <td>Vikas Gupta</td>
                            <td>Completed</td>
                            <td>01:10 PM</td>
                        </tr>
                        <tr>
                            <td>Rahul Sharma</td>
                            <td>9876543210</td>
                            <td>Amit Patel</td>
                            <td>Approved</td>
                            <td>10:15 AM</td>
                        </tr>
                        <tr>
                            <td>Priya Mehta</td>
                            <td>9988776655</td>
                            <td>Neha Shah</td>
                            <td>Pending</td>
                            <td>11:00 AM</td>
                        </tr>
                        <tr>
                            <td>Arjun Nair</td>
                            <td>9123456780</td>
                            <td>Ravi Kumar</td>
                            <td>Checked In</td>
                            <td>11:40 AM</td>
                        </tr>
                        <tr>
                            <td>Sneha Joshi</td>
                            <td>9012345678</td>
                            <td>Vikas Gupta</td>
                            <td>Completed</td>
                            <td>01:10 PM</td>
                        </tr>
                        <tr>
                            <td>Rahul Sharma</td>
                            <td>9876543210</td>
                            <td>Amit Patel</td>
                            <td>Approved</td>
                            <td>10:15 AM</td>
                        </tr>
                        <tr>
                            <td>Priya Mehta</td>
                            <td>9988776655</td>
                            <td>Neha Shah</td>
                            <td>Pending</td>
                            <td>11:00 AM</td>
                        </tr>
                        <tr>
                            <td>Arjun Nair</td>
                            <td>9123456780</td>
                            <td>Ravi Kumar</td>
                            <td>Checked In</td>
                            <td>11:40 AM</td>
                        </tr>
                        <tr>
                            <td>Sneha Joshi</td>
                            <td>9012345678</td>
                            <td>Vikas Gupta</td>
                            <td>Completed</td>
                            <td>01:10 PM</td>
                        </tr>
                    </tbody>
                </x-table>
>>>>>>> origin/TE-T672-feature/resident-profile
        </x-form.form-section>
    </div>
@endsection
