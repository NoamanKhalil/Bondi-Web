# Can my Mac run it? LLM RAM calculator for Mac

> Pick your Mac's memory and chip to see which local AI models it can run, how much RAM each LLM needs, and how fast: Llama, Qwen, Gemma, DeepSeek and gpt-oss.

**Quick answer:** A local AI model needs about its download size in memory, plus up to 20% for the conversation. An 8 GB Mac runs models up to about 7B parameters, 16 GB up to 14B, 32 GB up to about 30B, and 64 GB runs 70B models, tightly. How fast it writes depends on the chip's memory bandwidth.

Web page: https://trybondi.app/can-my-mac-run-it/

Press your Mac's memory, and its chip if you know it. The calculator sorts 48 popular local AI models into three groups: the ones that run well, the ones that fit if you close other apps, and the ones that won't fit. Every size is the real download from Ollama's library, and the rules come from real readings on a Mac, described below.

## How much RAM do you need to run a local LLM?

A local AI model (an LLM, or large language model) has to sit in your Mac's memory while it works. So the first number to know is its download size: Llama 3.1 8B is 4.9 GB in Ollama, Llama 3.3 70B is 42.5 GB.

On top of that, it needs room for the conversation (the context) and a little working space. In our reading, Dolphin Llama 3 8B (a 4.66 GB download) took **5.66 GB** of memory with an 8,192-token conversation. The calculator allows 20% of the model, up to 3 GB, plus 0.25 GB, which comes to 5.84 GB for that model. Longer conversations need more.

Then there's how much of the memory the model can use. Macs share one pool of memory between the processor and the graphics chip, which is why they're good at this. But macOS keeps part of it back: on our 64 GB M1 Max, it let the graphics chip use **51.8 GB (81%)** by default. Macs with less memory may get a smaller share.

So the calculator says:

- **Runs well:** the model needs no more than 65% of your memory, leaving the rest for macOS and your apps.
- **Fits, tightly:** it needs up to 81%. It loads, but close other apps first, or your Mac will start to swap and everything slows down.
- **Won't fit:** more than that. Ollama can still run part of it on the processor instead, but it's many times slower.

## How fast will it run?

Every new word (a token, about three-quarters of a word) means reading the working part of the model from memory once. So speed depends on the chip's **memory bandwidth**, which Apple publishes for each chip: 120 GB/s for M4, 273 GB/s for M4 Pro, up to 546 GB/s for M4 Max, and 1.2 TB/s for M5 Ultra.

In our readings, on an M1 Max (400 GB/s):

| Model | Download | In memory | Writing speed |
| --- | --- | --- | --- |
| Dolphin Llama 3 8B (Q4_0) | 4.66 GB | 5.66 GB, with an 8,192-token conversation | 39.5 tokens/s |
| Qwen2.5 0.5B (Q8_0) | 0.40 GB | 0.93 GB, with a 32,768-token conversation | 218 tokens/s |

The 8B model reached 46% of the theoretical limit (400 GB/s ÷ 4.66 GB), so the calculator's speeds are 46% of each chip's limit. Treat them as estimates: very small models are held back by other work, and long conversations slow every model down. Reading speed is about 5 tokens a second, so anything above 20 feels quick.

Mixture-of-experts models, such as gpt-oss and Qwen3 30B A3B, are a special case. All of the model has to fit in memory, but only a small part of it works on each word, so they write much faster than their size suggests.

## Every model, and the Mac it needs

Sizes are Ollama's default download of each model (usually 4-bit, Q4_K_M), checked on October 15, 2026. LM Studio's versions of the same models can be a little bigger or smaller.

<!-- models:table -->
| Model | Download | Memory it needs | Runs well on | Fits, tightly, on |
| --- | --- | --- | --- | --- |
| Gemma 3 1B | 0.8 GB | 1.2 GB | 8 GB | 8 GB |
| DeepSeek-R1 1.5B | 1.1 GB | 1.6 GB | 8 GB | 8 GB |
| Llama 3.2 1B | 1.3 GB | 1.8 GB | 8 GB | 8 GB |
| Qwen 3.5 0.8B | 1.3 GB | 1.8 GB | 8 GB | 8 GB |
| Llama 3.2 3B | 2.0 GB | 2.7 GB | 8 GB | 8 GB |
| Phi-4 mini 3.8B | 2.5 GB | 3.2 GB | 8 GB | 8 GB |
| Qwen3 4B | 2.5 GB | 3.2 GB | 8 GB | 8 GB |
| Qwen 3.5 2B | 2.7 GB | 3.5 GB | 8 GB | 8 GB |
| Qwen 3.5 4B | 3.3 GB | 4.2 GB | 8 GB | 8 GB |
| Gemma 3 4B | 3.4 GB | 4.3 GB | 8 GB | 8 GB |
| Mistral 7B | 4.4 GB | 5.5 GB | 8 GB | 8 GB |
| Gemma 4 E2B | 4.6 GB | 5.8 GB | 16 GB | 8 GB |
| DeepSeek-R1 7B | 4.7 GB | 5.9 GB | 16 GB | 8 GB |
| Llama 3.1 8B | 4.9 GB | 6.2 GB | 16 GB | 8 GB |
| DeepSeek-R1 8B | 5.2 GB | 6.5 GB | 16 GB | 8 GB |
| Qwen3 8B | 5.2 GB | 6.5 GB | 16 GB | 8 GB |
| Qwen 3.5 9B | 6.6 GB | 8.1 GB | 16 GB | 16 GB |
| Gemma 4 E4B | 6.6 GB | 8.2 GB | 16 GB | 16 GB |
| Gemma 4 12B | 8.0 GB | 9.9 GB | 16 GB | 16 GB |
| Gemma 3 12B | 8.2 GB | 10.0 GB | 16 GB | 16 GB |
| DeepSeek-R1 14B | 9.0 GB | 11.0 GB | 16 GB | 16 GB |
| Phi-4 14B | 9.1 GB | 11.1 GB | 16 GB | 16 GB |
| Qwen3 14B | 9.3 GB | 11.4 GB | 18 GB | 16 GB |
| gpt-oss 20B (mixture of experts, 3.6B active) | 13.8 GB | 16.8 GB | 32 GB | 24 GB |
| Devstral 24B | 14.3 GB | 17.5 GB | 32 GB | 24 GB |
| Mistral Small 3.2 24B | 15.2 GB | 18.4 GB | 32 GB | 24 GB |
| Gemma 3 27B | 17.4 GB | 20.7 GB | 32 GB | 24 GB |
| Qwen 3.5 27B | 17.5 GB | 20.7 GB | 32 GB | 24 GB |
| Qwen 3.8 27B | 17.7 GB | 21.0 GB | 32 GB | 32 GB |
| Qwen 3.6 27B | 17.8 GB | 21.0 GB | 32 GB | 32 GB |
| Qwen3 30B A3B (mixture of experts, 3B active) | 18.6 GB | 21.8 GB | 32 GB | 32 GB |
| Qwen3-Coder 30B A3B (mixture of experts, 3.3B active) | 18.6 GB | 21.8 GB | 32 GB | 32 GB |
| Gemma 4 26B A4B (mixture of experts, 4B active) | 18.7 GB | 22.0 GB | 32 GB | 32 GB |
| DeepSeek-R1 32B | 19.9 GB | 23.1 GB | 36 GB | 32 GB |
| Qwen3 32B | 20.2 GB | 23.5 GB | 36 GB | 32 GB |
| Gemma 4 31B | 20.4 GB | 23.6 GB | 36 GB | 32 GB |
| Qwen 3.5 35B A3B (mixture of experts, 3B active) | 22.1 GB | 25.3 GB | 48 GB | 32 GB |
| Qwen 3.6 35B A3B (mixture of experts, 3B active) | 22.6 GB | 25.9 GB | 48 GB | 32 GB |
| DeepSeek-R1 70B | 42.5 GB | 45.8 GB | 96 GB | 64 GB |
| Llama 3.3 70B | 42.5 GB | 45.8 GB | 96 GB | 64 GB |
| gpt-oss 120B (mixture of experts, 5.1B active) | 65.4 GB | 68.6 GB | 128 GB | 96 GB |
| Llama 4 Scout (mixture of experts, 17B active) | 67.4 GB | 70.7 GB | 128 GB | 96 GB |
| Qwen 3.5 122B A10B (mixture of experts, 10B active) | 81.4 GB | 84.6 GB | 128 GB | 128 GB |
| Qwen3 235B A22B (mixture of experts, 22B active) | 142.2 GB | 145.4 GB | 256 GB | 192 GB |
| Llama 3.1 405B | 243.1 GB | 246.3 GB | 512 GB | 512 GB |
| Qwen3-Coder 480B A35B (mixture of experts, 35B active) | 290.1 GB | 293.3 GB | 512 GB | 512 GB |
| DeepSeek-V3 671B (mixture of experts, 37B active) | 404.4 GB | 407.7 GB | more than 512 GB | 512 GB |
| DeepSeek-R1 671B (mixture of experts, 37B active) | 404.4 GB | 407.7 GB | more than 512 GB | 512 GB |
<!-- /models:table -->

## Questions

### How much RAM do I need to run a local LLM?

About the model's download size, plus up to 20% for the conversation. As a rule of thumb, an 8 GB Mac runs models up to about 7B parameters, 16 GB up to 14B, 32 GB up to about 30B, and 64 GB runs 70B models, tightly. The calculator above checks 48 popular models against your Mac's memory.

### What LLM can I run on my Mac?

It depends on your Mac's memory. With 8 GB: Llama 3.2 3B, Gemma 3 4B, Qwen 3.5 4B or Mistral 7B. With 16 GB: Qwen 3.5 9B, Gemma 4 12B, Phi-4 14B or DeepSeek-R1 14B. With 32 GB: gpt-oss 20B, Gemma 3 27B, Qwen 3.6 27B or Qwen3 30B A3B. With 64 GB: Llama 3.3 70B or DeepSeek-R1 70B, tightly. With 128 GB: gpt-oss 120B or Qwen 3.5 122B A10B.

### Can a Mac mini run LLMs?

Yes. A Mac mini uses the same unified memory as every Apple silicon Mac, so what it can run depends only on its memory and chip: with 16 GB, models up to about 14B parameters; with 64 GB, 70B models fit, tightly. Pick its memory and chip in the calculator above.

### How much RAM do I need for Llama 3.3 70B?

Llama 3.3 70B is a 42.5 GB download in Ollama and needs about 45.8 GB of memory. It fits, tightly, on a 64 GB Mac and runs well on 96 GB or more. On an M1 Max it would write about 4 tokens a second, by our estimate.

### Is 8 GB of RAM enough to run an LLM?

For small models, yes. An 8 GB Mac runs models such as Llama 3.2 3B, Gemma 3 4B and Mistral 7B well, and fits most other 7B and 8B models, such as Llama 3.1 8B, tightly: close other apps first.

### Why does my Mac get slow when a model is loaded?

A loaded model holds its memory even when you're not using it. If it takes most of your Mac's memory, macOS starts compressing memory and swapping to disk, and every app slows down. Quit or unload the model when you're done. [Bondi](https://trybondi.app/) shows each loaded model in Ollama or LM Studio with the memory it holds, and can unload it.

### Does this page check my Mac?

No. Websites can't see how much memory a Mac has, so you choose it. Everything is worked out in your browser, and nothing is sent anywhere.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
