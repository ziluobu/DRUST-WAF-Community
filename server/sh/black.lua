local http=require("socket.http");

local var_ip = m.getvar("REMOTE_ADDR");
local var_id = m.getvar("RULE.id");
local request_body = [[ip=]] .. var_ip .. [[&id=]] ..var_id
local response_body = {}

local res, code, response_headers = http.request{
  url = "http://127.0.0.1:10088/api/black",
  method = "POST",
  headers =
    {
        ["Content-Type"] = "application/x-www-form-urlencoded";
        ["Content-Length"] = #request_body;
    },
    source = ltn12.source.string(request_body),
    sink = ltn12.sink.table(response_body),
}

