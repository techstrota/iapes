@php
    $record = $getRecord();
    $userId = $record->username ?: $record->email;
    $plainPassword = $record->plain_password ?: str($record->username)->before('@user.com')->toString();
    $hashPassword = $record->password;
@endphp

<div style="display: flex; flex-direction: column; gap: 14px; width: 100%; box-sizing: border-box;"
     wire:key="intern-credentials-{{ $record->id }}-{{ md5($plainPassword) }}"
     x-data="{
         showPassword: false,
         copiedUser: false,
         copiedPass: false,
         userId: @js($userId),
         plainPass: @js($plainPassword),
         copyUser() {
             navigator.clipboard.writeText(this.userId);
             this.copiedUser = true;
             setTimeout(() => this.copiedUser = false, 2000);
         },
         copyPass() {
             navigator.clipboard.writeText(this.plainPass);
             this.copiedPass = true;
             setTimeout(() => this.copiedPass = false, 2000);
         }
     }">

    <!-- ── 1. User ID Row ── -->
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #fbbf24; display: flex; align-items: center; gap: 6px;">
                <svg style="width: 14px; height: 14px; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Login User ID
            </span>
            <button type="button" 
                    @click="copyUser()" 
                    style="background: transparent; border: none; font-size: 11px; font-weight: 600; color: #fbbf24; cursor: pointer; padding: 2px 6px; border-radius: 4px; transition: all 0.15s ease;"
                    onmouseover="this.style.textDecoration='underline';"
                    onmouseout="this.style.textDecoration='none';">
                <span x-show="!copiedUser">Copy</span>
                <span x-show="copiedUser" x-cloak style="color: #4edea3; font-weight: 700;">✓ Copied!</span>
            </button>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; background-color: #090e1c; border: 1.5px solid #222a3d; border-radius: 10px; padding: 8px 12px; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4); transition: border-color 0.2s ease;"
             onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.5)';"
             onmouseout="this.style.borderColor='#222a3d';">
            <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13.5px; font-weight: 600; color: #dae2fd; user-select: all; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $userId }}
            </span>
            <button type="button" 
                    @click="copyUser()" 
                    style="background: #17223b; border: 1px solid #222a3d; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #b8c4ff; cursor: pointer; flex-shrink: 0; margin-left: 8px; transition: all 0.15s ease;"
                    onmouseover="this.style.backgroundColor='#1e293b'; this.style.color='#ffffff'; this.style.borderColor='#3b82f6';"
                    onmouseout="this.style.backgroundColor='#17223b'; this.style.color='#b8c4ff'; this.style.borderColor='#222a3d';"
                    title="Copy User ID">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- ── 2. Password Row ── -->
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                <svg style="width: 14px; height: 14px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Password
            </span>
            <span style="font-size: 10.5px; font-weight: 600; padding: 2px 8px; border-radius: 4px; border: 1px solid #222a3d; background-color: #171f33;"
                  :style="showPassword ? 'color: #fbbf24; border-color: rgba(245, 158, 11, 0.35); background-color: rgba(245, 158, 11, 0.12);' : 'color: #94a3b8; border-color: #222a3d; background-color: #171f33;'"
                  x-text="showPassword ? 'Plaintext Active' : 'Encrypted (Hash)'">
            </span>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; background-color: #090e1c; border: 1.5px solid #222a3d; border-radius: 10px; padding: 8px 12px; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4); transition: border-color 0.2s ease;"
             onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.5)';"
             onmouseout="this.style.borderColor='#222a3d';">
            <!-- Password Text / Dots -->
            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">
                <template x-if="!showPassword">
                    <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 15px; letter-spacing: 0.22em; color: #94a3b8; user-select: none;">
                        ••••••••••••
                    </span>
                </template>
                <template x-if="showPassword">
                    <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13.5px; font-weight: 700; color: #4edea3; user-select: all;" 
                          x-text="plainPass">
                    </span>
                </template>
            </div>

            <!-- Action Controls: Eye toggle + Copy + Change Password -->
            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                <!-- Eye Toggle -->
                <button type="button" 
                        @click="showPassword = !showPassword" 
                        style="background: #17223b; border: 1px solid #222a3d; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #b8c4ff; cursor: pointer; transition: all 0.15s ease;"
                        onmouseover="this.style.backgroundColor='#1e293b'; this.style.color='#ffffff'; this.style.borderColor='#3b82f6';"
                        onmouseout="this.style.backgroundColor='#17223b'; this.style.color='#b8c4ff'; this.style.borderColor='#222a3d';"
                        :title="showPassword ? 'Hide Plain Password' : 'Reveal Plain Password'">
                    <template x-if="!showPassword">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </template>
                    <template x-if="showPassword">
                        <svg style="width: 14px; height: 14px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </template>
                </button>

                <!-- Copy Plain Password -->
                <button type="button" 
                        @click="copyPass()" 
                        style="background: #17223b; border: 1px solid #222a3d; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #b8c4ff; cursor: pointer; transition: all 0.15s ease;"
                        onmouseover="this.style.backgroundColor='#1e293b'; this.style.color='#ffffff'; this.style.borderColor='#3b82f6';"
                        onmouseout="this.style.backgroundColor='#17223b'; this.style.color='#b8c4ff'; this.style.borderColor='#222a3d';"
                        title="Copy Plain Password">
                    <template x-if="!copiedPass">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </template>
                    <template x-if="copiedPass">
                        <svg style="width: 14px; height: 14px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                </button>

                <!-- Change Password Icon Button -->
                <button type="button" 
                        wire:click="mountAction('changePassword')" 
                        style="background: #17223b; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #fbbf24; cursor: pointer; transition: all 0.15s ease;"
                        onmouseover="this.style.backgroundColor='rgba(245, 158, 11, 0.25)'; this.style.color='#fde68a'; this.style.borderColor='#f59e0b';"
                        onmouseout="this.style.backgroundColor='#17223b'; this.style.color='#fbbf24'; this.style.borderColor='rgba(245, 158, 11, 0.4)';"
                        title="Change Intern Password">
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ── 3. Change Password Action Row ── -->
    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 4px; border-top: 1px dashed rgba(255, 255, 255, 0.08); margin-top: 2px;">
        <button type="button" 
                wire:click="mountAction('changePassword')"
                style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 11.5px; font-weight: 600; color: #fbbf24; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; cursor: pointer; transition: all 0.2s ease;"
                onmouseover="this.style.background='rgba(245, 158, 11, 0.22)'; this.style.borderColor='rgba(245, 158, 11, 0.6)'; this.style.transform='translateY(-1px)';"
                onmouseout="this.style.background='rgba(245, 158, 11, 0.12)'; this.style.borderColor='rgba(245, 158, 11, 0.35)'; this.style.transform='translateY(0)';"
                title="Change Intern Login Password">
            <svg style="width: 14px; height: 14px; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Change Password
        </button>

        <span style="font-size: 10.5px; color: #94a3b8; display: flex; align-items: center; gap: 4px;">
            <svg style="width: 12px; height: 12px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Portal Sync Ready
        </span>
    </div>

    <!-- ── 4. Subtle Portal Notice ── -->
    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #94a3b8; padding-top: 2px;">
        <span style="color: #60a5fa;">ℹ️</span>
        <span>Credentials used by intern to authenticate at the Intern Portal.</span>
    </div>
</div>
