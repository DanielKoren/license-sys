#include "http.hpp"

#define port INTERNET_DEFAULT_HTTP_PORT
#define flags (INTERNET_FLAG_KEEP_CONNECTION)

const char* accept_types[] = { "text/*", NULL };
const char* user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36";

HTTP::HTTP(const std::string& domain)
	: m_domain(domain)
{
	// docs.microsoft.com/en-us/windows/win32/api/wininet/nf-wininet-internetopena
	// initialises internal data structs etc..
	m_internet_handle = InternetOpen(user_agent, INTERNET_OPEN_TYPE_DIRECT, nullptr, nullptr, 0);
	if (!m_internet_handle)
	{
		//printf("InternetOpen failed. [%d]\n", GetLastError());
		return;
	}
}

HTTP::~HTTP()
{
	if (m_request_handle)
		InternetCloseHandle(m_request_handle);
	if (m_connection_handle)
		InternetCloseHandle(m_connection_handle);
	if (m_internet_handle)
		InternetCloseHandle(m_internet_handle);
}

bool HTTP::send_request(const std::string& webpage, request_type type)
{
	if (type == request_type::get)
		m_req_type = "GET";
	else
		m_req_type = "POST";

	// docs.microsoft.com/en-us/windows/win32/api/wininet/nf-wininet-internetconnecta
	// opens http session
	m_connection_handle = InternetConnect(m_internet_handle, m_domain.c_str(), port, NULL, NULL, INTERNET_SERVICE_HTTP, 0, 0);
	if (!m_connection_handle)
	{
		//printf("InternetConnect failed. [%d]\n", GetLastError());
		return false;
	}

	// docs.microsoft.com/en-us/windows/win32/api/wininet/nf-wininet-httpopenrequesta
	// create http request
	m_request_handle = HttpOpenRequest(m_connection_handle, m_req_type.c_str(), webpage.c_str(), 0, 0, accept_types, flags, 0);
	if (!m_request_handle)
	{
		//printf("HttpOpenRequest failed. [%d]\n", GetLastError());
		return false;
	}

	// send the request
	auto status = HttpSendRequest(m_request_handle, m_headers.c_str(), m_headers.size(), const_cast<char*>(m_data.c_str()), m_data.size());
	if (!status)
	{
		//printf("HttpSendRequestA failed. [ 0x%x ]\n", GetLastError());
		return false;
	}

	return true;
}

std::string HTTP::get_url()
{
	std::string result;

	auto len = (DWORD)(2048 * sizeof(char));
	auto url = (LPTSTR)malloc(len);

	auto status = InternetQueryOption(m_request_handle, INTERNET_OPTION_URL, url, &len);
	if (!status)
	{
		//printf("InternetQueryOption failed. [0x%x]\n", GetLastError());
		free(url);
		return result;
	}

	result = url;
	free(url);

	return result;
}

std::string HTTP::read_data()
{
	std::string data;

	char buffer[1024]{};
	DWORD bytes_read = 0;

	while (InternetReadFile(m_request_handle, buffer, 1024, &bytes_read) && bytes_read)
	{
		data.append(buffer, bytes_read);
	}

	return data;
}

DWORD HTTP::get_status_code()
{
	DWORD status_code = 0;
	DWORD length = sizeof(status_code);

	auto status = HttpQueryInfoA(m_request_handle, HTTP_QUERY_STATUS_CODE | HTTP_QUERY_FLAG_NUMBER, &status_code, &length, NULL);
	if (!status)
	{
		//printf("HttpQueryInfoA failed. [ 0x%x ]\n", GetLastError());
		return 0;
	}

	return status_code;
}