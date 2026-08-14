# Employee Request Filing / Approver Selection Foundation

This phase provides the reusable filing foundation without implementing Leave.

## Done

- Authenticated User to active Employee requester resolution.
- Active, non-archived, `can_approve_requests` approver picker with server-side search.
- Immediate Supervisor and Department Head suggestions without automatic route insertion.
- Ordered employee-selected route preview with workflow min/max enforcement.
- Automatic HR Final Approval preview and runtime submission through `ApprovalRequestSubmissionService`.
- Generic development harness at `/approval-demo` and read-only confirmation pages.
- Final regression verification: 170 tests passed and 645 assertions.

## Not yet done

- Actual Leave request module.
- Employee Documents and Emergency Contact workflows.
- Notifications, Roles & Permissions, and Dashboard integration.

The demo requires exactly one active `approval_workflows` row with `module_key = approval_demo` and an eligible HR final approver when HR final approval is enabled.
