#!/usr/bin/env python3

import os
import sys
from dotenv import load_dotenv

ENV_PATH = "/var/www/html/swissmadecorp/.env"
DATA_PATH = "/var/www/html/swissmadecorp/public/python/data.txt"

def main():
    if len(sys.argv) < 2 or not sys.argv[1].strip():
        print('Usage: python chatgpt.py "your question"', file=sys.stderr)
        return 1

    if not os.path.isfile(ENV_PATH):
        raise RuntimeError(f".env file not found: {ENV_PATH}")

    load_dotenv(dotenv_path=ENV_PATH, override=True)

    api_key = os.getenv("CHATGPT_API_KEY")
    if not api_key:
        raise RuntimeError("CHATGPT_API_KEY is missing or empty in .env")

    os.environ["OPENAI_API_KEY"] = api_key

    if not os.path.isfile(DATA_PATH):
        raise RuntimeError(f"Data file not found: {DATA_PATH}")

    from langchain.document_loaders import TextLoader
    from langchain.indexes import VectorstoreIndexCreator
    from langchain.chat_models import ChatOpenAI

    query = sys.argv[1].strip()

    loader = TextLoader(DATA_PATH, encoding="utf-8")
    index = VectorstoreIndexCreator().from_loaders([loader])

    llm = ChatOpenAI(
        temperature=0,
        max_tokens=500,
        max_retries=0,
        request_timeout=60,
    )

    answer = index.query(query, llm=llm)
    print(answer)
    return 0

if __name__ == "__main__":
    try:
        sys.exit(main())
    except Exception as exc:
        print(f"Error: {exc}", file=sys.stderr)
        sys.exit(1)