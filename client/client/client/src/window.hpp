#pragma once

#include <windows.h>
#include <string>

class window
{
public:
	window(const HINSTANCE instance, const DWORD width, const DWORD height);
	~window();

	bool create_window(WNDPROC wndproc);
	bool is_running();
	bool get_message();
	
	std::string get_error();

	static HINSTANCE m_instance;

	static HWND m_hwnd_username;
	static HWND m_hwnd_password;
	static HWND m_hwnd_button;

private:
	std::string m_error;
	std::string m_classname;
	DWORD		m_width;
	DWORD		m_height;
	HWND		m_hwnd;
	MSG			m_msg;

};