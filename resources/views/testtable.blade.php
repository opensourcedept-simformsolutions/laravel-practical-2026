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
        </x-form.form-section>
    </div>
@endsection
