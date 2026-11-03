<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="IDS Log Analyzer — a deterministic, forward chaining intrusion detection system for Linux server logs, administered through Filament 5.">

        <title>{{ config('app.name', 'IDS Log Analyzer') }} — Deterministic Intrusion Detection</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#F2F2ED] font-sans text-black antialiased selection:bg-black selection:text-white">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:z-50 focus:border focus:border-black focus:bg-white focus:px-4 focus:py-2 focus:font-mono focus:text-xs focus:uppercase">
            Skip to content
        </a>

        <div class="mx-auto w-full max-w-[1600px] border-x border-black">
            {{-- Top navigation --}}
            <header class="grid grid-cols-2 border-b border-black md:grid-cols-[1fr_auto_auto]">
                <div class="flex items-center gap-3 px-4 py-4 sm:px-6">
                    <span class="h-2.5 w-2.5 shrink-0 bg-[#E10600]" aria-hidden="true"></span>
                    <span class="text-xs font-bold tracking-[-0.01em] uppercase sm:text-sm">IDS Log Analyzer</span>
                    <span class="hidden border-l border-black pl-3 font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase sm:inline">
                        Intrusion detection / v1.0
                    </span>
                </div>

                <div class="hidden items-center border-l border-black px-5 font-mono text-[10px] tracking-[0.18em] uppercase md:flex">
                    <span class="mr-2 h-1.5 w-1.5 bg-[#E10600]" aria-hidden="true"></span>
                    Status: Operational
                </div>

                <a
                    href="{{ url('/admin') }}"
                    class="flex items-center justify-center gap-3 border-l border-black bg-black px-5 py-4 font-mono text-[10px] font-bold tracking-[0.14em] text-white uppercase hover:bg-[#E10600] focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-white sm:px-8 sm:text-xs"
                >
                    Access Dashboard
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </header>

            <main id="main">
                {{-- Hero --}}
                <section class="border-b border-black">
                    <div class="grid grid-cols-1 lg:grid-cols-12">
                        <div class="px-4 pt-14 pb-10 sm:px-6 sm:pt-20 lg:col-span-8 lg:px-10 lg:py-20 xl:px-14">
                            <p class="flex items-center gap-3 font-mono text-[10px] tracking-[0.22em] uppercase">
                                <span class="h-2 w-2 shrink-0 bg-[#E10600]" aria-hidden="true"></span>
                                Intrusion detection system / Log analyzer
                            </p>

                            <h1 class="mt-8 text-[clamp(2.5rem,7.5vw,7rem)] leading-[0.84] font-bold tracking-[-0.055em] uppercase">
                                Deterministic<br>
                                threat<br>
                                detection.
                            </h1>

                            <div class="mt-10 grid max-w-2xl grid-cols-1 gap-8 border-t border-black pt-6 sm:grid-cols-[1fr_auto] sm:items-end">
                                <p class="text-base leading-[1.45] sm:text-lg">
                                    A forward chaining inference engine for Linux server logs. Events are evaluated
                                    in sequence against explicit rule sets; an alert is raised only when the complete
                                    condition chain resolves as true.
                                </p>

                                <a
                                    href="#workflow"
                                    class="inline-flex w-fit items-center gap-3 border border-black px-5 py-3 font-mono text-[10px] font-bold tracking-[0.16em] uppercase hover:bg-black hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
                                >
                                    Review the workflow
                                    <span aria-hidden="true">&darr;</span>
                                </a>
                            </div>
                        </div>

                        <aside class="grid grid-rows-4 divide-y divide-black border-t border-black lg:col-span-4 lg:border-t-0 lg:border-l" aria-label="System specifications">
                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">01 / Detection latency</span>
                                <span class="text-3xl font-bold tracking-[-0.04em] sm:text-4xl">&lt; 50ms</span>
                            </div>

                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">02 / Operating environment</span>
                                <span class="text-3xl font-bold tracking-[-0.04em] sm:text-4xl">Linux</span>
                            </div>

                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">03 / Inference mode</span>
                                <span class="text-3xl font-bold tracking-[-0.04em] sm:text-4xl">Sequential</span>
                            </div>

                            <div class="flex h-full flex-col justify-between gap-8 px-4 py-6 sm:px-6 lg:px-8">
                                <span class="font-mono text-[10px] tracking-[0.2em] text-black/60 uppercase">04 / Administration</span>
                                <span class="text-3xl font-bold tracking-[-0.04em] sm:text-4xl">Filament 5</span>
                            </div>
                        </aside>
                    </div>

                    <div class="grid grid-cols-1 divide-y divide-black border-t border-black md:grid-cols-4 md:divide-x md:divide-y-0">
                        <div class="flex items-baseline justify-between gap-4 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Events indexed</span>
                            <span class="font-mono text-xs font-bold">1,284,930</span>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Active rules</span>
                            <span class="font-mono text-xs font-bold">312</span>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Alerts / 24h</span>
                            <span class="font-mono text-xs font-bold">17</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6">
                            <span class="font-mono text-[10px] tracking-[0.16em] text-black/60 uppercase">Engine status</span>
                            <span class="flex items-center gap-2 font-mono text-xs font-bold">
                                <span class="h-1.5 w-1.5 bg-[#E10600]" aria-hidden="true"></span>
                                Active
                            </span>
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
                            Three modules / Production
                        </span>
                    </div>

                    <div class="grid grid-cols-1 divide-y divide-black border-b border-black md:grid-cols-3 md:divide-x md:divide-y-0">
                        <article class="flex flex-col p-4 pt-10 sm:p-6 md:p-8 lg:p-10">
                            <div class="flex items-start justify-between font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase">
                                <span>Module / 01</span>
                                <span class="h-2 w-2 bg-[#E10600]" aria-hidden="true"></span>
                            </div>

                            <h3 class="mt-20 max-w-[16ch] text-2xl leading-[0.95] font-bold tracking-[-0.03em] uppercase sm:text-3xl">
                                Forward chaining inference engine
                            </h3>

                            <p class="mt-6 text-sm leading-[1.6]">
                                Sequential logic evaluation for automated threat detection. Each event is tested
                                against an ordered rule base; a rule fires only when every antecedent condition is
                                satisfied. Derived facts feed the next evaluation cycle.
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
                                <span class="h-2 w-2 bg-[#E10600]" aria-hidden="true"></span>
                            </div>

                            <h3 class="mt-20 max-w-[16ch] text-2xl leading-[0.95] font-bold tracking-[-0.03em] uppercase sm:text-3xl">
                                Filament 5 admin panel
                            </h3>

                            <p class="mt-6 text-sm leading-[1.6]">
                                Native TALL-stack administration. Expert rules, log analysis, and scan results are
                                managed inside one Filament 5 panel. Authentication, authorization, tables, and forms
                                remain framework-native.
                            </p>

                            <div class="mt-8 border border-black">
                                <div class="flex justify-between gap-4 border-b border-black p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Expert rules</span>
                                    <span class="font-bold">Managed</span>
                                </div>
                                <div class="flex justify-between gap-4 border-b border-black p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Log analyzer</span>
                                    <span class="font-bold">Workspace</span>
                                </div>
                                <div class="flex justify-between gap-4 p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Scan results</span>
                                    <span class="font-bold">Reviewable</span>
                                </div>
                            </div>

                            <dl class="mt-auto border-t border-black pt-4 font-mono text-[10px] tracking-[0.12em] uppercase">
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Panel</dt>
                                    <dd class="font-bold">Filament 5</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Stack</dt>
                                    <dd class="font-bold">TALL</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Access</dt>
                                    <dd class="font-bold">Authenticated</dd>
                                </div>
                            </dl>
                        </article>

                        <article class="flex flex-col p-4 pt-10 sm:p-6 md:p-8 lg:p-10">
                            <div class="flex items-start justify-between font-mono text-[10px] tracking-[0.18em] text-black/60 uppercase">
                                <span>Module / 03</span>
                                <span class="h-2 w-2 bg-[#E10600]" aria-hidden="true"></span>
                            </div>

                            <h3 class="mt-20 max-w-[16ch] text-2xl leading-[0.95] font-bold tracking-[-0.03em] uppercase sm:text-3xl">
                                High-performance architecture
                            </h3>

                            <p class="mt-6 text-sm leading-[1.6]">
                                A lightweight Laravel application for constrained Linux hosts. Stream-oriented
                                parsing and bounded rule evaluation keep memory use predictable as log volume
                                increases.
                            </p>

                            <div class="mt-8 border border-black">
                                <div class="border-b border-black p-3">
                                    <div class="flex items-end justify-between gap-4">
                                        <span class="font-mono text-[10px] tracking-[0.12em] text-black/60 uppercase">Memory profile</span>
                                        <span class="font-mono text-[10px] font-bold tracking-[0.12em] uppercase">Low</span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-10 gap-px" aria-hidden="true">
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 bg-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                        <span class="h-3 border border-black"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between gap-4 p-3 font-mono text-[10px] tracking-[0.12em] uppercase">
                                    <span class="text-black/60">Parsing</span>
                                    <span class="font-bold">Stream-oriented</span>
                                </div>
                            </div>

                            <dl class="mt-auto border-t border-black pt-4 font-mono text-[10px] tracking-[0.12em] uppercase">
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Runtime</dt>
                                    <dd class="font-bold">PHP 8.5</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-1.5">
                                    <dt class="text-black/60">Footprint</dt>
                                    <dd class="font-bold">Low</dd>
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
                            Three stages / Linear
                        </span>
                    </div>

                    <div class="divide-y divide-black border-b border-black">
                        <div class="grid grid-cols-1 md:grid-cols-[5rem_1fr_16rem]">
                            <div class="border-b border-black p-4 font-mono text-2xl font-bold tracking-[-0.04em] md:border-r md:border-b-0 md:p-6">
                                01
                            </div>
                            <div class="p-4 md:p-8">
                                <h3 class="text-xl font-bold tracking-[-0.02em] uppercase sm:text-2xl">Log ingestion</h3>
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
                                <h3 class="text-xl font-bold tracking-[-0.02em] uppercase sm:text-2xl">Forward chaining evaluation</h3>
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
                                <span class="h-2 w-2 bg-[#E10600]" aria-hidden="true"></span>
                            </div>
                            <div class="p-4 md:p-8">
                                <h3 class="text-xl font-bold tracking-[-0.02em] uppercase sm:text-2xl">Threat alert</h3>
                                <p class="mt-3 max-w-2xl text-sm leading-[1.6]">
                                    A conclusion is written as a scan result with the complete rule trace. Analysts
                                    review the evidence inside the Filament panel.
                                </p>
                            </div>
                            <div class="border-t border-black p-4 font-mono text-[10px] leading-[1.8] tracking-[0.14em] text-black/60 uppercase md:flex md:flex-col md:justify-center md:border-t-0 md:border-l md:p-6">
                                Output / Alert &middot; Rule trace
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Closing CTA --}}
                <section class="grid grid-cols-1 border-b border-black bg-black text-white lg:grid-cols-2">
                    <div class="border-b border-white p-6 sm:p-10 lg:border-r lg:border-b-0 lg:p-14">
                        <p class="flex items-center gap-3 font-mono text-[10px] tracking-[0.22em] uppercase">
                            <span class="h-2 w-2 shrink-0 bg-[#E10600]" aria-hidden="true"></span>
                            System status / Ready
                        </p>
                        <h2 class="mt-8 max-w-[14ch] text-4xl leading-[0.9] font-bold tracking-[-0.045em] uppercase sm:text-5xl lg:text-6xl">
                            Audit the rule. Verify the alert.
                        </h2>
                    </div>

                    <div class="flex flex-col justify-between gap-10 p-6 sm:p-10 lg:p-14">
                        <p class="max-w-md text-sm leading-[1.6]">
                            Every detection output remains traceable to an explicit rule and its evaluated
                            conditions. Review rules, logs, and scan results in the Filament panel.
                        </p>

                        <a
                            href="{{ url('/admin') }}"
                            class="flex items-center justify-between gap-4 border border-white bg-white px-5 py-4 font-mono text-[11px] font-bold tracking-[0.16em] text-black uppercase hover:border-[#E10600] hover:bg-[#E10600] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            Access Dashboard
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </section>
            </main>

            {{-- Footer --}}
            <footer class="grid grid-cols-1 font-mono text-[10px] tracking-[0.16em] uppercase sm:grid-cols-[1fr_auto]">
                <p class="px-4 py-5 sm:px-6">
                    &copy; {{ date('Y') }} IDS Log Analyzer. All rights reserved.
                </p>
                <p class="border-t border-black px-4 py-5 text-black/60 sm:border-t-0 sm:border-l sm:px-6">
                    Laravel 13 / Filament 5 / Tailwind CSS
                </p>
            </footer>
        </div>
    </body>
</html>
