#include "config.hpp"
#include "json.hpp"
#include <fstream>

std::string get_config_path()
{
	char path[MAX_PATH]{};
	if (!GetTempPath(MAX_PATH, path)) {
		//printf("GetTempPath failed, error: %d\n", GetLastError());
		return "";
	}

	strcat_s(path, MAX_PATH, "jeiSVnuC.txt");
	return path;
}

bool config::remember_me = false;
std::string config::username = "";
std::string config::password = "";

bool config::save(std::string username, std::string password)
{
	std::ofstream file(get_config_path());
	if (file.is_open()) 
	{
		using namespace json11;

		const Json obj = Json::object({
				{ "username", username },
				{ "password", password }
			});
		file << obj.dump();
		return true;
	}

	return false;
}

bool config::load()
{
	std::ifstream file(get_config_path());
	if (file.is_open())
	{	
		using namespace json11;

		std::string file_content;
		std::getline(file, file_content, '\0');

		std::string err;
		const auto json = Json::parse(file_content, err);
		if (err.empty())
		{
			username = json["username"].string_value();
			password = json["password"].string_value();
			
			return true;
		}
	}

	return false;
}

void config::remove()
{
	std::remove(get_config_path().c_str());
}
