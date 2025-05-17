<div class="modal fade" id="inactiveModal" tabindex="-1" aria-labelledby="inactiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inactiveModalLabel">Set Employee Inactive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inactiveForm">
                    <div class="mb-3">
                        <label for="employeeIdInactive" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employeeIdInactive" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="employeeNameInactive" class="form-label">Employee Name</label>
                        <input type="text" class="form-control" id="employeeNameInactive" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="inactiveReason" class="form-label">Reason for Setting Inactive</label>
                        <textarea class="form-control" id="inactiveReason" rows="3" placeholder="e.g., Employee resignation, temporary leave" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="setEmployeeInactive()">Set Inactive</button>
            </div>
        </div>
    </div>
</div>
