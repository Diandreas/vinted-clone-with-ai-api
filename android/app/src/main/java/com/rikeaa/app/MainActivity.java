package com.rikeaa.app;

import android.content.Intent;
import android.os.Bundle;
import androidx.annotation.Nullable;
import androidx.core.view.WindowCompat;
import com.getcapacitor.BridgeActivity;
import com.google.android.play.core.appupdate.AppUpdateInfo;
import com.google.android.play.core.appupdate.AppUpdateManager;
import com.google.android.play.core.appupdate.AppUpdateManagerFactory;
import com.google.android.play.core.install.model.AppUpdateType;
import com.google.android.play.core.install.model.UpdateAvailability;

public class MainActivity extends BridgeActivity {
    private static final int UPDATE_REQUEST_CODE = 9911;
    private AppUpdateManager appUpdateManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        // Prevent WebView from drawing behind the status bar
        WindowCompat.setDecorFitsSystemWindows(getWindow(), true);

        appUpdateManager = AppUpdateManagerFactory.create(this);
        checkForImmediateUpdate();
    }

    @Override
    public void onResume() {
        super.onResume();
        resumeImmediateUpdateIfInProgress();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == UPDATE_REQUEST_CODE) {
            // If update was cancelled or failed, re-check to enforce immediate update.
            checkForImmediateUpdate();
        }
    }

    private void checkForImmediateUpdate() {
        if (appUpdateManager == null) return;
        appUpdateManager.getAppUpdateInfo().addOnSuccessListener(appUpdateInfo -> {
            if (appUpdateInfo.updateAvailability() == UpdateAvailability.UPDATE_AVAILABLE
                && appUpdateInfo.isUpdateTypeAllowed(AppUpdateType.IMMEDIATE)) {
                try {
                    appUpdateManager.startUpdateFlowForResult(
                        appUpdateInfo,
                        AppUpdateType.IMMEDIATE,
                        this,
                        UPDATE_REQUEST_CODE
                    );
                } catch (android.content.IntentSender.SendIntentException e) {
                    // If we cannot start the update flow, retry later on next resume.
                }
            }
        });
    }

    private void resumeImmediateUpdateIfInProgress() {
        if (appUpdateManager == null) return;
        appUpdateManager.getAppUpdateInfo().addOnSuccessListener(appUpdateInfo -> {
            if (appUpdateInfo.updateAvailability()
                == UpdateAvailability.DEVELOPER_TRIGGERED_UPDATE_IN_PROGRESS) {
                try {
                    appUpdateManager.startUpdateFlowForResult(
                        appUpdateInfo,
                        AppUpdateType.IMMEDIATE,
                        this,
                        UPDATE_REQUEST_CODE
                    );
                } catch (android.content.IntentSender.SendIntentException e) {
                    // Retry on next resume.
                }
            }
        });
    }
}
