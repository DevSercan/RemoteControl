import requests

class ApiClient:
    def __init__(self, baseUrl: str, apiKey: str):
        self.baseUrl = baseUrl
        self.apiKey = apiKey
        self.endpoints = ["command", "data"]

    def _request(self, method: str, endpoint: str, payload: dict=None) -> dict:
        try:
            url = f"{self.baseUrl}/{endpoint}"
            headers = {
                'Content-Type': 'application/json',
                'Authorization': f'Bearer {self.apiKey}'
            }
            response = requests.request(method, url, json=payload, headers=headers)
            return response.json()
        except Exception as e:
            return {"error": str(e)}

    def get(self, endpoint: str) -> dict:
        return self._request("GET", endpoint)

    def post(self, endpoint: str, payload: dict) -> dict:
        return self._request("POST", endpoint, payload)

    def put(self, endpoint: str, payload: dict) -> dict:
        return self._request("PUT", endpoint, payload)

    def delete(self, endpoint: str) -> dict:
        return self._request("DELETE", endpoint)
