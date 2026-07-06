import "./bootstrap";
import "./sidebar";
import "./validations/user";
import "./validations/complaint-validation";
import "./validations/delivery";
import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

$(document).ready(function () {
    // Helper: Update sidebar notification badge via AJAX
    function fetchAndUpdateSidebarBadge() {
        $.ajax({
            url: '/notifications',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                let sidebarLink = $('.sidebar-menu a[href*="/notifications"]');
                if (sidebarLink.length > 0) {
                    let label = sidebarLink.find('.sidebar-link-label');
                    let badge = label.find('.sidebar-unread-badge');
                    let unread = response.unread_count;
                    if (unread > 0) {
                        if (badge.length > 0) {
                            badge.text(unread);
                        } else {
                            label.append(`<span class="badge bg-danger rounded-pill badge-pulse ms-1 sidebar-unread-badge">${unread}</span>`);
                        }
                    } else {
                        badge.remove();
                    }
                }
            }
        });
    }

    if (window.Echo) {
        // If user is resident and flatId is defined
        if (window.userRole === 'resident' && window.flatId) {
            console.log('Echo: Subscribing to resident flat channel: flat.' + window.flatId);
            window.Echo.private(`flat.${window.flatId}`)
                .listen('.visitor.approval.requested', (e) => {
                    console.log('Echo: VisitorApprovalRequested received:', e);
                    
                    // Update the sidebar badge
                    fetchAndUpdateSidebarBadge();

                    // Refresh list if on notifications page
                    if (typeof window.loadNotifications === 'function') {
                        window.loadNotifications(window.location.href);
                    }

                    window.currentPendingVisitorLogId = e.id;
                    
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
                        showDenyButton: true,
                        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Approve',
                        denyButtonText: '<i class="bi bi-x-circle me-1"></i>Reject',
                        cancelButtonText: '<i class="bi bi-clock me-1"></i>Decide Later',
                        confirmButtonColor: '#198754', // green
                        denyButtonColor: '#dc3545', // red
                        cancelButtonColor: '#6c757d', // gray
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                    }).then((result) => {
                        let status = '';
                        if (result.isConfirmed) {
                            status = 'approve';
                        } else if (result.isDenied) {
                            status = 'reject';
                        } else {
                            console.log('Echo: Modal dismissed, user will decide later.');
                            return;
                        }

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
                                // Update sidebar badge
                                fetchAndUpdateSidebarBadge();
                                
                                if (typeof rd === 'function') {
                                    rd();
                                }
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
                })
                .listen('.visitor.approval.recalled', (e) => {
                    console.log('Echo: VisitorApprovalRecalled received:', e);
                    
                    // Update sidebar badge
                    fetchAndUpdateSidebarBadge();

                    // Refresh list if on notifications page
                    if (typeof window.loadNotifications === 'function') {
                        window.loadNotifications(window.location.href);
                    }

                    if (Swal.isVisible() && Swal.getTitle() && Swal.getTitle().textContent.includes('Visitor Approval Request')) {
                        Swal.close();
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'info',
                                title: `Visitor request for "${e.visitor_name}" was recalled/corrected.`
                            });
                        }
                    }
                    if (typeof rd === 'function') {
                        rd();
                    }
                })
                .listen('.visitor.approval.status.updated', (e) => {
                    console.log('Echo: VisitorApprovalStatusUpdated received for flat:', e);
                    
                    // Update sidebar badge
                    fetchAndUpdateSidebarBadge();

                    // Refresh list if on notifications page
                    if (typeof window.loadNotifications === 'function') {
                        window.loadNotifications(window.location.href);
                    }

                    if (window.currentPendingVisitorLogId === e.id) {
                        if (Swal.isVisible() && Swal.getTitle() && Swal.getTitle().textContent.includes('Visitor Approval Request')) {
                            Swal.close();
                        }
                        window.currentPendingVisitorLogId = null;
                    }
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: (e.status === 'approved' || e.status === 'entered') ? 'success' : 'error',
                            title: `Visitor "${e.visitor_name}" has been ${e.status.toUpperCase()} by ${e.approver_name}!`
                        });
                    }
                    if (typeof rd === 'function') {
                        rd();
                    }
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
                            icon: (e.status === 'approved' || e.status === 'entered') ? 'success' : 'error',
                            title: `Visitor "${e.visitor_name}" for flat ${e.flat_name} has been ${e.status.toUpperCase()} by ${e.approver_name}!`
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
