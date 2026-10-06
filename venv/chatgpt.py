#!/usr/bin/env python3
import os
from dotenv import load_dotenv

env_path = "/var/www/html/swissmadecorp/.env"

if not os.path.isfile(env_path):
    raise RuntimeError(f".env file not found: {env_path}")

load_dotenv(dotenv_path=env_path, override=True)

api_key = os.getenv("CHATGPT_API_KEY")

if not api_key:
    raise RuntimeError("CHATGPT_API_KEY is missing or empty")

print(f"Loaded configuration from: {env_path}")
print(f"Loaded key ending in: ...{api_key[-4:]}")
print("No API requests were made.")