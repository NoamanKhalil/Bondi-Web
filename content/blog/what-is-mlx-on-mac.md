---
title: What is MLX on Mac? MLX vs GGUF, and which is faster
description: MLX is Apple's framework for running AI models on Apple silicon. What MLX models are, how MLX differs from GGUF, Ollama and llama.cpp, and which runs faster on a Mac.
answer: MLX is Apple's open-source framework for running AI models on Apple silicon Macs, built around their unified memory. An MLX model is a model saved in MLX's format, usually 4-bit or 8-bit. GGUF is the other common format, from llama.cpp, and runs on any computer. In our test on an M1 Max, the same model wrote about 23% faster as MLX than as GGUF.
date: 2026-10-16
topic: memory
status: draft
order: 30
related: unified-memory-mac, which-app-is-using-my-mac-memory, memory-pressure-explained
---
MLX is Apple's open-source framework for running AI models on Macs with Apple silicon (M1 and later). Apple's machine learning research team released it in December 2023. It's built around the Mac's **unified memory**: the processor and the graphics share one pool of memory, so MLX never has to copy a model from one to the other.

When people say "an MLX model", they mean a model saved in MLX's format, ready to run with MLX on a Mac. You'll see them in LM Studio and on Hugging Face, often with "MLX" and "4bit" or "8bit" in the name.

## Key points

- MLX is Apple's framework for running AI models on Apple silicon; an MLX model is one saved in its format.
- GGUF is the other common format. It comes from llama.cpp and runs on Macs, Windows and Linux.
- The same model takes about the same memory in either format, at the same quality (4-bit or 8-bit).
- In our test on an M1 Max, the same model wrote about 23% faster as MLX than as GGUF.
- LM Studio runs both formats on a Mac. Ollama's library now lists MLX versions of some models too.

## What is an MLX model?

A model is a very large set of numbers (its parameters). To run it, an app needs those numbers in a file format it understands, and an engine to do the maths. MLX models are stored as **safetensors** files that the MLX engine reads, usually squeezed to 4-bit or 8-bit to save memory. The "mlx-community" group on Hugging Face publishes MLX versions of many popular open models.

The 4-bit or 8-bit is how many bits each parameter is stored in, and it sets the size. On one Mac we looked at, LM Studio listed Qwen 3.8 27B at **29.53 GB** as an 8-bit MLX model and **15.15 GB** as a 4-bit one: half the bits, about half the size.

## MLX vs GGUF

GGUF is the file format of **llama.cpp**, the engine behind most local AI apps. It runs on almost any computer, which is why most downloads are GGUF.

| | MLX | GGUF |
| --- | --- | --- |
| Made by | Apple (open source, MIT license) | The llama.cpp project (open source) |
| Runs on | Macs with Apple silicon | Macs, Windows and Linux |
| Used by | LM Studio and mlx-lm; Ollama lists MLX versions of some models | Ollama, LM Studio, llama.cpp and most other apps |
| Size | About the same at the same quality | About the same at the same quality |

At the same quality, the size is about the same. Ollama's library has both for Gemma 4 (checked October 16, 2026):

| Model | GGUF download | MLX download |
| --- | --- | --- |
| Gemma 4 12B | 8.0 GB | 7.7 GB |
| Gemma 4 26B | 18.7 GB | 18.3 GB |
| Gemma 4 31B | 20.4 GB | 19.4 GB |

The GGUF downloads are a little bigger here because they include extras, such as a small helper model that speeds up writing. Either way, the model needs that much memory and more while it runs: the [LLM RAM calculator](/can-my-mac-run-it/) works out how much for your Mac.

## Is MLX faster than GGUF on a Mac?

We ran the same model both ways on one Mac: a 14-inch MacBook Pro with M1 Max and 64 GB, in LM Studio 0.4.25, on October 16, 2026. The model was a community version of Qwen 3.8 27B at 4-bit, saved once as MLX and once as GGUF (Q4_K_M), loaded with the same 8,192-token context and asked the same question three times.

| | Writing speed, three runs | Average | Memory in use |
| --- | --- | --- | --- |
| MLX | 13.2, 12.2, 11.7 tokens/s | **12.4 tokens/s** | 14.1 GB |
| GGUF (llama.cpp) | 10.1, 10.3, 10.0 tokens/s | **10.1 tokens/s** | 15.7 GB |

On this Mac, the MLX version wrote **about 23% faster** and held a little less memory (as LM Studio reported it). That's one model on one Mac: the gap depends on the model, the chip and the app, so if speed matters, try the model you use both ways.

## MLX vs Ollama and LM Studio

MLX isn't an app you chat in. It's the engine underneath. To use MLX models:

- **LM Studio** runs MLX models on Apple silicon Macs. When you search for a model, pick a version marked MLX.
- **Ollama** lists MLX versions of some models in its library, with "mlx" in the name, such as `gemma4:12b-mlx`.
- **mlx-lm**, Apple's own Python package, runs and fine-tunes MLX models from Terminal, for people comfortable with Python.

## How much memory does an MLX model need?

About its download size, plus room for the conversation, the same as any other format. A 4-bit model is about a third the size of the full 16-bit one, and an 8-bit model about half. If a model takes most of your Mac's memory, everything else slows down: see [What is unified memory on a Mac?](/blog/unified-memory-mac/) for how the memory is shared.

## Questions

### What is MLX on Mac?

Apple's open-source framework for running AI models on Apple silicon Macs. An MLX model is a model saved in its format, ready to run in apps such as LM Studio.

### What is the difference between MLX and GGUF?

MLX is Apple's format and engine, made for Apple silicon Macs. GGUF is the format of llama.cpp and runs on any computer. At the same quality, a model takes about the same memory in either.

### Is MLX faster than GGUF on a Mac?

In our test on an M1 Max with 64 GB, the same 27B model at 4-bit wrote 12.4 tokens a second as MLX and 10.1 as GGUF: about 23% faster. The gap depends on the model, the chip and the app.

### Does Ollama support MLX?

Ollama's library now lists MLX versions of some models, with "mlx" in the name, such as gemma4:12b-mlx. Most of its models are GGUF.

### What does 4-bit or 8-bit mean in an MLX model?

How many bits each of the model's parameters is stored in. 8-bit is about half the full size and very close to full quality; 4-bit is about a third of the full size, for a small loss in quality.
