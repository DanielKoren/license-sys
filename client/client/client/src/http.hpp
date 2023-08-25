#pragma once

#include <Windows.h>
#include <WinInet.h>
#include <string>

#pragma comment(lib, "wininet.lib")

enum class request_type
{
	get,
	post
};

class HTTP
{
public:
	HTTP(const std::string& domain);
	~HTTP();

	bool send_request(const std::string& webpage, request_type type = request_type::get);
	std::string get_url();
	std::string read_data();
	DWORD get_status_code();

	inline void set_headers(const std::string& headers) { m_headers = headers; }
	inline void set_data(const std::string& data) { m_data = data; }

private:
	HINTERNET m_internet_handle;
	HINTERNET m_connection_handle;
	HINTERNET m_request_handle;

	std::string m_domain;
	std::string m_req_type;
	std::string m_headers;
	std::string m_data;

};