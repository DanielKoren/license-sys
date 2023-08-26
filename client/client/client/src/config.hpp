#pragma once

#include <Windows.h>
#include <string>

namespace config
{
	extern bool remember_me;
	extern std::string username;
	extern std::string password;

	bool save(std::string username, std::string password);
	bool load();
	void remove();
}
