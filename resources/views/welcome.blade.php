<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="IDS Log Analyzer — an academic expert system demonstrating forward chaining for rule-based threat detection.">

        <title>{{ config('app.name', 'IDS Log Analyzer') }} — Expert System Implementation</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-white font-sans text-black antialiased selection:bg-black selection:text-white">
        <div class="mx-auto w-full max-w-[1600px] border-x border-black">
            {{-- Top navigation --}}
            <header class="grid grid-cols-2 border-b border-black md:grid-cols-[1fr_auto_auto]">
                <div class="flex items-center gap-3 px-4 py-4 sm:px-6">
                    <span class="h-2.5 w-2.5 shrink-0 bg-black" aria-hidden="true"></span>
                    <span class="text-xs font-bold tracking-[-0.01em] uppercase sm:text-sm">IDS Log Analyzer - Expert System</span>
                    <span class="hidden border-l border-black pl-3 font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase sm:inline">
                        Academic system documentation
                    </span>
                </div>

                <div class="hidden items-center border-l border-black px-5 font-mono text-[10px] tracking-[0.18em] uppercase md:flex">
                    <span class="mr-2 h-1.5 w-1.5 bg-black" aria-hidden="true"></span>
                    Academic implementation
                </div>

                <a
                    href="{{ url('/admin') }}"
                    class="flex items-center justify-center gap-3 border-l border-black bg-black px-5 py-4 font-mono text-[10px] font-bold tracking-[0.14em] text-white uppercase hover:bg-white hover:text-black focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-black sm:px-8 sm:text-xs"
                >
                    Open system interface
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </header>

            <main id="main">
                {{-- Hero --}}
                <section class="border-b border-black">
                    <div class="grid grid-cols-1 lg:grid-cols-12">
                        <div class="px-4 pt-14 pb-10 sm:px-6 sm:pt-20 lg:col-span-8 lg:px-10 lg:py-20 xl:px-14">
                            <p class="flex items-center gap-3 font-mono text-[10px] tracking-[0.22em] uppercase">
                                <span class="h-2 w-2 shrink-0 bg-black" aria-hidden="true"></span>
                                Artificial intelligence assignment / Expert system
                            </p>

                            <h1 class="mt-8 text-[clamp(2.5rem,7.5vw,7rem)] leading-[0.84] font-bold tracking-[-0.055em] uppercase">
                                Expert<br>
                                system<br>
                                implementation.
                            </h1>

                            <div class="mt-10 grid max-w-2xl grid-cols-1 gap-8 border-t border-black pt-6 sm:grid-cols-[1fr_auto] sm:items-end">
                                <p class="text-base leading-[1.45] sm:text-lg">
                                    An expert system for Linux server logs. Events are normalized into facts and
                                    evaluated through forward chaining against an explicit knowledge base. A threat
                                    conclusion is produced only when the required rule conditions are satisfied.
                                </p>

                                <a
                                    href="#workflow"
                                    class="inline-flex w-fit items-center gap-3 border border-black px-5 py-3 font-mono text-[10px] font-bold tracking-[0.16em] uppercase hover:bg-black hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
                                >
                                    Review execution path
                                    <span aria-hidden="true">&darr;</span>
                                </a>
                            </div>
                        </div>

                        <aside class="grid grid-rows-4 divide-y divide-black border-t border-black lg:col-span-4 lg:border-t-0 lg:border-l" aria-label="Inference architecture">
                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">01 / Knowledge base</span>
                                <span class="text-2xl leading-none font-bold tracking-[-0.04em] uppercase sm:text-3xl">Explicit rules</span>
                            </div>

                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">02 / Working memory</span>
                                <span class="text-2xl leading-none font-bold tracking-[-0.04em] uppercase sm:text-3xl">Derived facts</span>
                            </div>

                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">03 / Inference engine</span>
                                <span class="text-2xl leading-none font-bold tracking-[-0.04em] uppercase sm:text-3xl">Forward chaining</span>
                            </div>

                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">04 / Review interface</span>
                                <span class="text-2xl leading-none font-bold tracking-[-0.04em] uppercase sm:text-3xl">Rule evaluation</span>
                            </div>
                        </aside>
                    </div>

                    <div class="grid grid-cols-1 divide-y divide-black border-t border-black md:grid-cols-4 md:divide-x md:divide-y-0" aria-label="System architecture overview">
                        <div class="flex flex-col gap-2 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Input</span>
                            <span class="text-sm font-bold uppercase">Structured log events</span>
                        </div>
                        <div class="flex flex-col gap-2 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Facts</span>
                            <span class="text-sm font-bold uppercase">Working memory update</span>
                        </div>
                        <div class="flex flex-col gap-2 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Rules</span>
                            <span class="text-sm font-bold uppercase">Condition evaluation</span>
                        </div>
                        <div class="flex flex-col gap-2 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Conclusion</span>
                            <span class="text-sm font-bold uppercase">Traceable alert result</span>
                        </div>
                    </div>
                </section>

                {{-- Capabilities --}}
                <section aria-labelledby="capabilities-heading">
                    <div class="grid grid-cols-[3.5rem_1fr] border-b border-black md:grid-cols-[5rem_1fr_auto]">
                        <span class="flex items-center border-r border-black px-4 py-3 font-mono text-[10px] tracking-[0.18em]">01</span>
                        <h2 id="capabilities-heading" class="px-4 py-3 font-mono text-[10px] font-bold tracking-[0.22em] uppercase sm:px-6">
                            Core capabilities
                        </h2>
                        <span class="hidden items-center border-l border-black px-5 py-3 font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase md:flex">
                            Three modules / Academic scope
                        </span>
                    </div>

                    <div class="grid grid-cols-1 divide-y divide-black border-b border-black md:grid-cols-3 md:divide-x md:divide-y-0">
                        <article class="flex flex-col p-4 pt-10 sm:p-6 md:p-8 lg:p-10">
                            <div class="flex items-start justify-between font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase">
                                <span>Module / 01</span>
                                <span class="h-2 w-2 bg-black" aria-hidden="true"></span>
                            </div>

                            <h3 class="mt-20 max-w-[16ch] text-2xl leading-[0.95] font-bold tracking-[-0.03em] uppercase sm:text-3xl">
                                Forward chaining logic
                            </h3>

                            <p class="mt-6 text-sm leading-[1.6]">
                                Antecedents are evaluated against the current facts in sequence. When every
                                condition in a rule is satisfied, its consequent is added to working memory and
                                becomes available to the next evaluation cycle.
                            </p>

                            <pre class="mt-8 overflow-x-auto border border-black p-4 font-mono text-[10px] leading-[1.7]">RULE 0042 / BRUTE FORCE
IF    auth.failures[5m] &gt; 5
AND   src.ip IN threat_feed
THEN  RAISE alert.brute_force</pre>

                            <dl class="mt-auto border-t border-black pt-4 font-mono text-[10px] tracking-[0.12em] uppercase">
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Execution</dt>
                                    <dd class="font-bold">Ordered</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Output</dt>
                                    <dd class="font-bold">Full rule trace</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Result</dt>
                                    <dd class="font-bold">Deterministic</dd>
                                </div>
                            </dl>
                        </article>

                        <article class="flex flex-col p-4 pt-10 sm:p-6 md:p-8 lg:p-10">
                            <div class="flex items-start justify-between font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase">
                                <span>Module / 02</span>
                                <span class="h-2 w-2 bg-black" aria-hidden="true"></span>
                            </div>

                            <h3 class="mt-20 max-w-[16ch] text-2xl leading-[0.95] font-bold tracking-[-0.03em] uppercase sm:text-3xl">
                                Fact normalization
                            </h3>

                            <p class="mt-6 text-sm leading-[1.6]">
                                Raw server log lines are parsed into a consistent representation of events,
                                attributes, and observations. These normalized facts become the initial state for
                                sequential rule evaluation.
                            </p>

                            <div class="mt-8 border border-black">
                                <div class="flex justify-between gap-4 border-b border-black p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Source</span>
                                    <span class="font-bold">Server logs</span>
                                </div>
                                <div class="flex justify-between gap-4 border-b border-black p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Transformation</span>
                                    <span class="font-bold">Fact extraction</span>
                                </div>
                                <div class="flex justify-between gap-4 p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Output</span>
                                    <span class="font-bold">Normalized facts</span>
                                </div>
                            </div>

                            <dl class="mt-auto border-t border-black pt-4 font-mono text-[10px] tracking-[0.12em] uppercase">
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Representation</dt>
                                    <dd class="font-bold">Structured</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Memory</dt>
                                    <dd class="font-bold">Initial facts</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Purpose</dt>
                                    <dd class="font-bold">Rule input</dd>
                                </div>
                            </dl>
                        </article>

                        <article class="flex flex-col p-4 pt-10 sm:p-6 md:p-8 lg:p-10">
                            <div class="flex items-start justify-between font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase">
                                <span>Module / 03</span>
                                <span class="h-2 w-2 bg-black" aria-hidden="true"></span>
                            </div>

                            <h3 class="mt-20 max-w-[16ch] text-2xl leading-[0.95] font-bold tracking-[-0.03em] uppercase sm:text-3xl">
                                Deterministic conclusions
                            </h3>

                            <p class="mt-6 text-sm leading-[1.6]">
                                When all antecedents in a rule are satisfied, the corresponding conclusion is
                                derived from the available facts. The process does not estimate probability or
                                introduce an unsupported interpretation.
                            </p>

                            <div class="mt-8 border border-black">
                                <div class="border-b border-black p-3">
                                    <div class="flex items-end justify-between gap-4">
                                        <span class="font-mono text-[10px] tracking-[0.12em] text-black/60 uppercase">Inference result</span>
                                        <span class="font-mono text-[10px] font-bold tracking-[0.12em] uppercase">Definitive</span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-10 gap-px" aria-hidden="true">
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between gap-4 p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Decision basis</span>
                                    <span class="font-bold">Satisfied antecedents</span>
                                </div>
                            </div>

                            <dl class="mt-auto border-t border-black pt-4 font-mono text-[10px] tracking-[0.12em] uppercase">
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Logic</dt>
                                    <dd class="font-bold">Rule-based</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Uncertainty</dt>
                                    <dd class="font-bold">Not applied</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Host</dt>
                                    <dd class="font-bold">Linux</dd>
                                </div>
                            </dl>
                        </article>
                    </div>
                </section>

                {{-- Workflow --}}
                <section id="workflow" aria-labelledby="workflow-heading">
                    <div class="grid grid-cols-[3.5rem_1fr] border-b border-black md:grid-cols-[5rem_1fr_auto]">
                        <span class="flex items-center border-r border-black px-4 py-3 font-mono text-[10px] tracking-[0.18em]">02</span>
                        <h2 id="workflow-heading" class="px-4 py-3 font-mono text-[10px] font-bold tracking-[0.22em] uppercase sm:px-6">
                            Execution path
                        </h2>
                        <span class="hidden items-center border-l border-black px-5 py-3 font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase md:flex">
                            Three stages / Forward chaining
                        </span>
                    </div>

                    <div class="divide-y divide-black border-b border-black">
                        <div class="grid grid-cols-1 md:grid-cols-[5rem_1fr_16rem]">
                            <div class="border-b border-black p-4 font-mono text-2xl font-bold tracking-[-0.04em] md:border-r md:border-b-0 md:p-6">
                                01
                            </div>
                            <div class="p-4 md:p-8">
                                <h3 class="text-xl font-bold tracking-[-0.02em] uppercase sm:text-2xl">Log ingestion &amp; parsing</h3>
                                <p class="mt-3 max-w-2xl text-sm leading-[1.6]">
                                    Raw log lines are read from configured sources, normalized into structured
                                    events, and persisted for evaluation.
                                </p>
                            </div>
                            <div class="border-t border-black p-4 font-mono text-[10px] leading-[1.8] tracking-[0.14em] text-black/60 uppercase md:flex md:flex-col md:justify-center md:border-t-0 md:border-l md:p-6">
                                Input / Auth &middot; Syslog &middot; Access
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[5rem_1fr_16rem]">
                            <div class="border-b border-black p-4 font-mono text-2xl font-bold tracking-[-0.04em] md:border-r md:border-b-0 md:p-6">
                                02
                            </div>
                            <div class="p-4 md:p-8">
                                <h3 class="text-xl font-bold tracking-[-0.02em] uppercase sm:text-2xl">Rule evaluation cycle</h3>
                                <p class="mt-3 max-w-2xl text-sm leading-[1.6]">
                                    Ordered rules are applied to each event. Satisfied conditions extend the working
                                    memory; unresolved chains terminate without raising an alert.
                                </p>
                            </div>
                            <div class="border-t border-black p-4 font-mono text-[10px] leading-[1.8] tracking-[0.14em] text-black/60 uppercase md:flex md:flex-col md:justify-center md:border-t-0 md:border-l md:p-6">
                                Engine / Sequential &middot; Deterministic
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[5rem_1fr_16rem]">
                            <div class="flex items-center gap-3 border-b border-black p-4 font-mono text-2xl font-bold tracking-[-0.04em] md:border-r md:border-b-0 md:p-6">
                                03
                                <span class="h-2 w-2 bg-black" aria-hidden="true"></span>
                            </div>
                            <div class="p-4 md:p-8">
                                <h3 class="text-xl font-bold tracking-[-0.02em] uppercase sm:text-2xl">Threat conclusion output</h3>
                                <p class="mt-3 max-w-2xl text-sm leading-[1.6]">
                                    A conclusion is written as a scan result with the complete rule trace. Analysts
                                    review the evidence and complete rule trace in the system interface.
                                </p>
                            </div>
                            <div class="border-t border-black p-4 font-mono text-[10px] leading-[1.8] tracking-[0.14em] text-black/60 uppercase md:flex md:flex-col md:justify-center md:border-t-0 md:border-l md:p-6">
                                Output / Alert &middot; Rule trace
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Academic review --}}
                <section class="grid grid-cols-1 border-b border-black bg-black text-white lg:grid-cols-2">
                    <div class="border-b border-white p-6 sm:p-10 lg:border-r lg:border-b-0 lg:p-14">
                        <p class="flex items-center gap-3 font-mono text-[10px] tracking-[0.22em] uppercase">
                            <span class="h-2 w-2 shrink-0 bg-white" aria-hidden="true"></span>
                            Academic project / System documentation
                        </p>
                        <h2 class="mt-8 max-w-[14ch] text-4xl leading-[0.9] font-bold tracking-[-0.045em] uppercase sm:text-5xl lg:text-6xl">
                            Inspect the rule.<br>Verify the conclusion.
                        </h2>
                    </div>

                    <div class="flex flex-col justify-between gap-10 p-6 sm:p-10 lg:p-14">
                        <p class="max-w-md text-sm leading-[1.6]">
                            Every detection output remains traceable to an explicit rule and its evaluated
                            conditions. Inspect the normalized facts, evaluated rules, and resulting threat conclusion.
                        </p>

                        <a
                            href="{{ url('/admin') }}"
                            class="flex items-center justify-between gap-4 border border-white bg-white px-5 py-4 font-mono text-[11px] font-bold tracking-[0.16em] text-black uppercase hover:bg-black hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            Open system interface
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </section>
            </main>

            {{-- Footer --}}
            <footer class="grid grid-cols-1 font-mono text-[10px] tracking-[0.16em] uppercase sm:grid-cols-[1fr_auto]">
                <p class="px-4 py-5 sm:px-6">
                    &copy; {{ date('Y') }} IDS Log Analyzer. Academic project.
                </p>
                <p class="border-t border-black px-4 py-5 text-black/60 sm:border-t-0 sm:border-l sm:px-6">
                    Artificial Intelligence Course / Expert Systems Assignment
                </p>
            </footer>
        </div>
    </body>
</html>
