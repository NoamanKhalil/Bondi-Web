# What is MLX on Mac? MLX vs GGUF, and which is faster

> MLX is Apple's framework for running AI models on Apple silicon. What MLX models are, how MLX differs from GGUF, Ollama and llama.cpp, and which runs faster on a Mac.

**Quick answer:** MLX is Apple's open-source framework for running AI models on Apple silicon Macs, built around their unified memory. An MLX model is a model saved in MLX's format, usually 4-bit or 8-bit. GGUF is the other common format, from llama.cpp, and runs on any computer. In our test on an M1 Max, the same model wrote about 23% faster as MLX than as GGUF.

By Noaman Khalil, maker of Bondi. Published October 16, 2026.
Web page: https://trybondi.app/blog/what-is-mlx-on-mac/

MLX is Apple's open-source framework for running AI models on Macs with Apple silicon (M1 and later), released by Apple's machine learning researchers in December 2023. It's built around the Mac's **unified memory**: the processor and graphics share one pool, so a model never has to be copied between them. An "MLX model" is a model saved in MLX's format, often with "MLX" and "4bit" or "8bit" in its name.

## Key points

- MLX is Apple's engine for AI on Apple silicon. GGUF, from llama.cpp, is the other common format and runs on any computer.
- At the same quality, a model takes about the same memory in either format.
- In our test on an M1 Max, the same model wrote about 23% faster as MLX than as GGUF.

## MLX vs GGUF

| | MLX | GGUF |
| --- | --- | --- |
| Made by | Apple (open source, MIT license) | The llama.cpp project (open source) |
| Runs on | Macs with Apple silicon | Macs, Windows and Linux |
| Used by | LM Studio and mlx-lm; Ollama lists MLX versions of some models | Ollama, LM Studio, llama.cpp and most other apps |

Sizes are close. Ollama's library has Gemma 4 both ways (checked October 16, 2026):

| Model | GGUF download | MLX download |
| --- | --- | --- |
| Gemma 4 12B | 8.0 GB | 7.7 GB |
| Gemma 4 26B | 18.7 GB | 18.3 GB |
| Gemma 4 31B | 20.4 GB | 19.4 GB |

The quality matters more than the format: on our test Mac, LM Studio listed Qwen 3.8 27B at **29.53 GB** as 8-bit MLX and **15.15 GB** as 4-bit. The [LLM RAM calculator](https://trybondi.app/can-my-mac-run-it/) works out what fits your Mac.

## Is MLX faster than GGUF on a Mac?

We ran a community version of Qwen 3.8 27B at 4-bit both ways, as MLX and as GGUF (Q4_K_M), in LM Studio 0.4.25 on a 14-inch MacBook Pro with M1 Max and 64 GB (October 16, 2026): same 8,192-token context, same question, three runs each.

| | Writing speed, three runs | Average | Memory in use |
| --- | --- | --- | --- |
| MLX | 13.2, 12.2, 11.7 tokens/s | **12.4 tokens/s** | 14.1 GB |
| GGUF (llama.cpp) | 10.1, 10.3, 10.0 tokens/s | **10.1 tokens/s** | 15.7 GB |

MLX wrote **about 23% faster** and held a little less memory, as LM Studio reported it. That's one model on one Mac; if speed matters, try yours both ways.

## How to use MLX

MLX is the engine, not an app you chat in:

- **LM Studio:** when you search for a model, pick a version marked MLX.
- **Ollama:** some models have an MLX version, such as `gemma4:12b-mlx`.
- **mlx-lm:** Apple's Python package, to run and fine-tune MLX models from Terminal.

## Questions

### What is the difference between MLX and GGUF?

MLX is Apple's format and engine, made for Apple silicon Macs. GGUF is llama.cpp's format and runs on any computer. At the same quality, a model takes about the same memory in either.

### Is MLX faster than GGUF on a Mac?

In our test on an M1 Max, the same 27B model at 4-bit wrote 12.4 tokens a second as MLX and 10.1 as GGUF: about 23% faster. The gap depends on the model, the chip and the app.

### Does Ollama support MLX?

Ollama's library lists MLX versions of some models, with "mlx" in the name, such as gemma4:12b-mlx. Most of its models are GGUF.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
