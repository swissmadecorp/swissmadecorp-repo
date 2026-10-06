#!/usr/local/bin/python3.11
import os
import sys
from dotenv import load_dotenv

# import constants
load_dotenv(override=True)

APIKEY = os.getenv("CHATGPT_API_KEY")
if not APIKEY:
    raise RuntimeError("GEMINI_API_KEY was not found in .env")

print(f"Loaded key ending in: ...{APIKEY[-4:]}")

from langchain.document_loaders import TextLoader
from langchain.indexes import VectorstoreIndexCreator
from langchain.llms import OpenAI
from langchain.chat_models import ChatOpenAI

os.environ["OPENAI_API_KEY"] = APIKEY

query = sys.argv[1]

loader = TextLoader("/var/www/html/swissmadecorp/public/python/data.txt")
index = VectorstoreIndexCreator().from_loaders([loader])

print(index.query(query, llm=ChatOpenAI()))