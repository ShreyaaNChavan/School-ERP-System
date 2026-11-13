<style>
.sn-page{font-family:'Inter',sans-serif;color:#25313C}
.sn-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:22px}
.sn-title{font-size:23px;font-weight:700;color:#123B5D;margin:0 0 5px}
.sn-sub{font-size:13px;color:#64748B;margin:0}
.sn-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 16px;border-radius:9px;background:#123B5D;color:#fff;font:600 13px 'Inter',sans-serif;text-decoration:none;border:1px solid #123B5D;cursor:pointer;white-space:nowrap;transition:.2s}
.sn-btn:hover{background:#176B87;color:#fff;border-color:#176B87}
.sn-btn-primary{background:#123B5D;color:#fff;border-color:#123B5D}
.sn-btn-outline{background:#fff;color:#123B5D;border-color:#CBD5E1}
.sn-btn-outline:hover{background:#F8FAFC;color:#123B5D;border-color:#CBD5E1}
.sn-card{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:19px;margin-bottom:18px}
.sn-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:20px}
.sn-kpi-card{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:17px 19px}
.sn-label{font-size:12px;color:#64748B;margin-bottom:9px}
.sn-kpi{font-size:25px;font-weight:700;color:#123B5D;margin-top:0}
.sn-section-title{font-size:15px;font-weight:700;color:#123B5D;margin:0 0 16px}
.sn-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.sn-field label{display:block;font-size:11px;font-weight:600;color:#64748B;margin-bottom:6px}
.sn-field input,.sn-field select,.sn-field textarea,.sn-filter input,.sn-filter select{width:100%;height:39px;box-sizing:border-box;border:1px solid #CBD5E1;border-radius:8px;padding:0 10px;font-size:12px;color:#25313C;background:#fff;outline:none}
.sn-field textarea{height:auto;min-height:90px;padding:10px}
.sn-field input:focus,.sn-field select:focus,.sn-field textarea:focus,.sn-filter input:focus,.sn-filter select:focus{border-color:#176B87}
.sn-field.full{grid-column:1/-1}
.sn-filter{display:grid;grid-template-columns:2fr repeat(2,minmax(130px,1fr)) auto;gap:10px;align-items:end;margin-bottom:18px}
.sn-filter button,.sn-filter .sn-btn{height:39px;min-height:39px;padding:0 13px;border-radius:8px;font-size:12px}
.sn-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.sn-actions a,.sn-actions button{font-family:'Inter',sans-serif}
.sn-action{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font:600 13px 'Inter',sans-serif;text-decoration:none;cursor:pointer;white-space:nowrap;transition:.2s}
.sn-action:hover{background:#F8FAFC;color:#123B5D}
.sn-danger{color:#DC2626}.sn-success{color:#16A34A}
.sn-table-wrap{overflow-x:auto}
.sn-table{width:100%;border-collapse:collapse;min-width:850px}
.sn-table th{padding:12px 13px;text-align:left;font-size:11px;font-weight:700;color:#64748B;background:#F8FAFC;border-bottom:1px solid #CBD5E1;white-space:nowrap}
.sn-table td{padding:13px;font-size:12px;border-bottom:1px solid #E2E8F0;vertical-align:middle}
.sn-table tr:last-child td{border-bottom:none}
.sn-muted{color:#64748B;font-size:11px;margin-top:4px}
.sn-badge{display:inline-block;padding:5px 9px;border-radius:20px;font-size:10px;font-weight:700;text-transform:capitalize;background:#E0F2FE;color:#176B87}
.sn-alert{padding:12px 15px;border-radius:9px;margin-bottom:15px;font-size:13px;background:#DCFCE7;color:#166534}
.sn-error{background:#FEF2F2;color:#991B1B}
.sn-empty{text-align:center;padding:45px 15px!important;color:#64748B}
.sn-pagination{padding-top:15px}
.sn-back{background:#fff;color:#123B5D;border:1px solid #CBD5E1}
.sn-back:hover{background:#F8FAFC;color:#123B5D;border-color:#CBD5E1}
@media(max-width:1100px){.sn-filter{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:700px){.sn-form{grid-template-columns:1fr}.sn-grid{grid-template-columns:1fr}.sn-filter{grid-template-columns:1fr 1fr}}
@media(max-width:450px){.sn-filter{grid-template-columns:1fr}}

.sn-head-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-left:auto;flex-wrap:wrap}
.sn-row-actions{display:flex;align-items:center;justify-content:flex-start;gap:8px;flex-wrap:nowrap;white-space:nowrap}
.sn-action-form{display:inline-flex;margin:0}
.sn-row-actions .sn-action{min-width:64px}
.sn-table td:last-child{white-space:nowrap}
@media(max-width:700px){.sn-head-actions{width:100%;justify-content:flex-start}.sn-row-actions{gap:6px}}

</style>
