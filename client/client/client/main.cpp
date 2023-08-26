#include <windows.h>
#include <vector>
#include <fstream>
#include "src/window.hpp"
#include "src/http.hpp"
#include "src/json.hpp"
#include "src/base64.hpp"
#include "src/config.hpp"

std::string get_hwid_token()
{
    DWORD volume_serial_num = 0;
    GetVolumeInformation("c:\\", NULL, 12, &volume_serial_num, NULL, NULL, NULL, 10);

    char str[64];
    _itoa_s(volume_serial_num, str, 16);
    
    return str;
}

LRESULT CALLBACK window_procedure(HWND hwnd, UINT msg, WPARAM wparam, LPARAM lparam)
{
    switch (msg) {
    case WM_CREATE: 
    {
        // username label
        CreateWindow("STATIC", "Username:", WS_VISIBLE | WS_CHILD, 50, 50, 100, 20, hwnd, NULL, window::m_instance, NULL);

        // password label
        CreateWindow("STATIC", "Password:", WS_VISIBLE | WS_CHILD, 50, 80, 100, 20, hwnd, NULL, window::m_instance, NULL);

        // username textbox
        window::m_hwnd_username = CreateWindow("EDIT", "", WS_VISIBLE | WS_CHILD | WS_BORDER, 160, 50, 150, 20, hwnd, NULL, window::m_instance, NULL);

        // password textbox
        window::m_hwnd_password = CreateWindow("EDIT", "", WS_VISIBLE | WS_CHILD | WS_BORDER | ES_PASSWORD, 160, 80, 150, 20, hwnd, NULL, window::m_instance, NULL);

        // login button
        window::m_hwnd_button = CreateWindow("BUTTON", "Login", WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON, 230, 120, 80, 30, hwnd, (HMENU)IDM_LOGIN, window::m_instance, NULL);

        // remember me checkbox
        window::m_hwnd_rememberme = CreateWindow("BUTTON", "Remember Me", WS_VISIBLE | WS_CHILD | BS_CHECKBOX, 50, 120, 120, 30, hwnd, (HMENU)IDM_REMEMBER_ME, window::m_instance, NULL);

        // check if config file exist & and load data
        if (config::load())
        {
            config::remember_me = true;
            CheckDlgButton(hwnd, IDM_REMEMBER_ME, BST_CHECKED);

            SetWindowText(window::m_hwnd_username, config::username.c_str());
            SetWindowText(window::m_hwnd_password, config::password.c_str());
        }
        
        break;
    }
    case WM_COMMAND: {
        // remember me
        if (HIWORD(wparam) == BN_CLICKED && LOWORD(wparam) == IDM_REMEMBER_ME) 
        {
            if (IsDlgButtonChecked(hwnd, IDM_REMEMBER_ME))
            {
                config::remember_me = false;
                CheckDlgButton(hwnd, IDM_REMEMBER_ME, BST_UNCHECKED);
            } 
            else 
            {
                config::remember_me = true;
                CheckDlgButton(hwnd, IDM_REMEMBER_ME, BST_CHECKED);
            }
        }

        // login button
        if (HIWORD(wparam) == BN_CLICKED && LOWORD(wparam) == IDM_LOGIN) 
        {
            char username[100], password[100];

            GetWindowText(window::m_hwnd_username, username, 100);
            if (strlen(username) == 0)
            {
                MessageBox(hwnd, "Empty username.", "Error", MB_ICONERROR | MB_OK);
                return 0;
            }

            GetWindowText(window::m_hwnd_password, password, 100);
            if (strlen(password) == 0) 
            {
                MessageBox(hwnd, "Empty password.", "Error", MB_ICONERROR | MB_OK);
                return 0;
            }

            // Save config file or remove fie
            if (config::remember_me)
                config::save(username, password);
            else 
                config::remove();

            // Generate HWID token
            const auto hwid = get_hwid_token();
            
            // Prepare our data & send post request to auth.php
            std::string post_data;
            post_data += "username=";
            post_data += username;
            post_data += "&password=";
            post_data += password;
            post_data += "&hwid=";
            post_data += hwid;
            
            HTTP http("localhost");
            http.set_headers("Content-Type: application/x-www-form-urlencoded");
            http.set_data(post_data);
            http.send_request("/license-sys/web/auth.php", request_type::post);
            
            // Check auth.php response & parse into JSON obj
            if (http.get_status_code() == 200)
            {
                auto response = http.read_data();
                //MessageBox(hwnd, response.c_str(), "INFO", MB_ICONINFORMATION | MB_OK);
                
                const auto size = response.size();
                if (response.size() > 0) 
                {
                    using namespace json11;
                    std::string err;
                    const auto json = Json::parse(response, err);
                    // show error msg if json parsing has failed 
                    if (!err.empty()) 
                    {
                        MessageBox(hwnd, err.c_str(), "Parsing Error", MB_ICONERROR | MB_OK);
                    }
                    else 
                    {
                        //MessageBox(hwnd, json.dump().c_str(), "JSON response", MB_ICONINFORMATION | MB_OK);

                        // check success key in the JSON response
                        if (json["success"].is_bool() && json["success"].bool_value()) {
                            // get our binary data and run it dynamically
                            const auto binary_data_encoded = json["data"].string_value();
                            const auto binary_data_decoded = base64_decode(binary_data_encoded);

                            // Check if the binary file is exe format
                            if (binary_data_decoded.at(0) == 0x4D && binary_data_decoded.at(1) == 0x5A) {
                                // 
                            }
                            else {
                                MessageBox(hwnd, "Invalid PE header", "Error", MB_ICONERROR | MB_OK);
                            }
                        } 
                        else {
                            MessageBox(hwnd, json["error_msg"].string_value().c_str(), "Error", MB_ICONERROR | MB_OK);
                        }
                    }
                }
            }
        }
        break;
    }
    case WM_DESTROY: 
    {
        PostQuitMessage(0);
        break;
    }
    default:
        return DefWindowProc(hwnd, msg, wparam, lparam);
    }
    return 0;
}

enum main_status : int
{
    err_success = 0x0,
    err_window = 1U << 0,		//0x00000001
    err_network = 1U << 1		//0x00000002
};

void error_msg(const char* message)
{
    MessageBox(GetActiveWindow(), message, "Error", MB_ICONERROR | MB_OK);
}

int WINAPI WinMain(HINSTANCE instance, HINSTANCE previnstance, LPSTR cmdline, int cmdshow)
{
    auto wnd = window(instance, 375, 240);
    if (!wnd.create_window(window_procedure))
    {
        error_msg(wnd.get_error().c_str());
        return err_window;
    }

    while (wnd.is_running())
    {
        if (wnd.get_message())
            continue;
    }

    return err_success;
}