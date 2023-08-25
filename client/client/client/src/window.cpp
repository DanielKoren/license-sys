#include "window.hpp"
#include <random>

HINSTANCE window::m_instance;

HWND window::m_hwnd_username;
HWND window::m_hwnd_password;
HWND window::m_hwnd_button;

std::string random_string(std::size_t length)
{
	static const std::string alphabet = "abcdefghijklmnopqrstuvwxyz";
	static std::default_random_engine rng(std::time(nullptr));
	static std::uniform_int_distribution<std::size_t> distribution(0, alphabet.size() - 1);

	std::string str;
	while (str.size() < length) str += alphabet[distribution(rng)];
	return str;
}

window::window(const HINSTANCE instance, const DWORD width, const DWORD height)
	: m_width(width), m_height(height)
{
	m_instance = instance;
	m_classname = random_string(10);
}

window::~window()
{
	if (m_hwnd)
		DestroyWindow(m_hwnd);

	UnregisterClass(m_classname.c_str(), m_instance);
}

bool window::create_window(WNDPROC wndproc)
{
	// register class
	auto icon_handle = LoadIcon(LoadLibraryA("SHELL32"), MAKEINTRESOURCE(29));

	WNDCLASSEX wndclass{};
	ZeroMemory(&wndclass, sizeof(WNDCLASSEX));
	wndclass.cbSize = sizeof(WNDCLASSEX);
	wndclass.style = CS_CLASSDC;
	wndclass.lpfnWndProc = wndproc;
	wndclass.cbClsExtra = 0;
	wndclass.cbWndExtra = 0;
	wndclass.hInstance = m_instance;
	wndclass.hIcon = icon_handle;
	wndclass.hCursor = LoadCursor(nullptr, IDC_ARROW);
	wndclass.hbrBackground = (HBRUSH)COLOR_WINDOW;
	wndclass.lpszMenuName = nullptr;
	wndclass.lpszClassName = m_classname.c_str();
	wndclass.hIconSm = NULL;

	if (!RegisterClassEx(&wndclass)) {
		m_error = "Failed registering class, last error: " + GetLastError();
		return false;
	}

	// create window 
	m_hwnd = CreateWindow(m_classname.c_str(),
		m_classname.c_str(),
		WS_SYSMENU | WS_CAPTION | WS_MINIMIZEBOX | WS_VISIBLE,
		(GetSystemMetrics(SM_CXSCREEN) / 2) - (m_width / 2), // center x
		(GetSystemMetrics(SM_CYSCREEN) / 2) - (m_height / 2), // center y
		m_width,
		m_height,
		0, 0,
		m_instance,
		nullptr);
	if (!m_hwnd) {
		m_error = "Failed creating window, last error: " + GetLastError();
		return false;
	}

	return true;
}

bool window::is_running()
{
	if (m_msg.message == WM_QUIT)
		return false;

	return true;
}

bool window::get_message()
{
	if (PeekMessage(&m_msg, NULL, 0U, 0U, PM_REMOVE))
	{
		TranslateMessage(&m_msg);
		DispatchMessage(&m_msg);
		return true;
	}

	return false;
}

std::string window::get_error()
{
	return m_error;
}