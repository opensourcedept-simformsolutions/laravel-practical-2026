import "./bootstrap";
import "./sidebar";
import "./validations/user";
import "./validations/complaint-validation";
import "./validations/delivery";
import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

$(document).ready(function () {
    if (window.Echo) {
        // If user is resident and flatId is defined
        if (window.userRole === 'resident' && window.flatId) {
            console.log('Echo: Subscribing to resident flat channel: flat.' + window.flatId);
            window.Echo.private(`flat.${window.flatId}`)
                .listen('.visitor.approval.requested', (e) => {
                    console.log('Echo: VisitorApprovalRequested received:', e);
                    
                    Swal.fire({
                        title: 'Visitor Approval Request',
                        html: `
                            <div class="text-start">
                                <p><strong>Visitor Name:</strong> ${e.visitor_name}</p>
                                <p><strong>Phone:</strong> ${e.visitor_phone}</p>
                                <p><strong>Purpose:</strong> ${e.purpose}</p>
                                <p><strong>Flat:</strong> ${e.flat_name}</p>
                                <p class="text-muted mt-2 small">Do you want to allow this visitor to enter your flat?</p>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Approve',
                        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Reject',
                        confirmButtonColor: '#198754', // green
                        cancelButtonColor: '#dc3545', // red
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    }).then((result) => {
                        let status = result.isConfirmed ? 'approve' : 'reject';
                        let url = `/passes/${e.id}/${status}`;
                        
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'PATCH'
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: response.message || 'Action completed successfully.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function (xhr) {
                                let message = xhr.responseJSON?.message || 'Something went wrong.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: message
                                });
                            }
                        });
                    });
                });
        }

        // If user is gatekeeper/admin and societyId is defined
        if ((window.userRole === 'gatekeeper' || window.userRole === 'admin') && window.societyId) {
            console.log('Echo: Subscribing to society channel: society.' + window.societyId);
            window.Echo.private(`society.${window.societyId}`)
                .listen('.visitor.approval.status.updated', (e) => {
                    console.log('Echo: VisitorApprovalStatusUpdated received:', e);
                    
                    // Show a Toast
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: e.status === 'approved' ? 'success' : 'error',
                            title: `Visitor "${e.visitor_name}" for flat ${e.flat_name} has been ${e.status.toUpperCase()}!`
                        });
                    }
                    
                    // If we are on the pending passes page (where the datatable is initialized as 'table')
                    if (typeof table !== 'undefined' && table !== null) {
                        console.log('Echo: Reloading visitor log datatable');
                        table.ajax.reload(null, false);
                    }
                });
        }
    }
});
